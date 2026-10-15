<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Middleware\ResolveGym;
use App\Http\Requests\Api\ConversationHistoryRequest;
use App\Http\Requests\Api\SendMessageRequest;
use App\Http\Requests\Api\SpeakMessageRequest;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\Gym;
use App\Modules\Assistant\Speech\SpeechSynthesizer;
use App\Modules\Assistant\Speech\SpeechText;
use App\Modules\Conversation\Actions\RememberUiContext;
use App\Modules\Conversation\Actions\ReplyToGuest;
use App\Modules\Conversation\Actions\StartConversation;
use App\Modules\Conversation\Support\MessagePresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ConciergeChatController extends Controller
{
    public function start(Request $request, StartConversation $start): JsonResponse
    {
        $conversation = $start->handle(ResolveGym::gym($request), ResolveGym::locale($request));

        return response()->json([
            'guest_token' => $conversation->guest_token,
            'locale' => $conversation->locale,
        ]);
    }

    public function message(SendMessageRequest $request, RememberUiContext $remember, ReplyToGuest $reply): JsonResponse
    {
        $gym = ResolveGym::gym($request);

        $conversation = $this->findConversation($gym, $request->validated('guest_token'));

        $remember->handle($gym, $conversation, $request->uiContext());

        try {
            $assistantMessage = $reply->handle($gym, $conversation, $request->validated('message'));
        } catch (\Throwable $e) {
            Log::error('Concierge reply failed', ['gym_id' => $gym->id, 'error' => $e->getMessage()]);

            return response()->json([
                'error' => 'concierge_unavailable',
                'message' => 'The AI Fitness Concierge is temporarily unavailable. Please try again in a moment.',
            ], 503);
        }

        return response()->json([
            'message' => MessagePresenter::format($assistantMessage),
            'status' => $conversation->fresh()->status,
        ]);
    }

    public function history(ConversationHistoryRequest $request): JsonResponse
    {
        $conversation = $this->findConversation(ResolveGym::gym($request), $request->validated('guest_token'));

        $messages = $conversation->messages()
            ->whereIn('role', [ConversationMessage::ROLE_GUEST, ConversationMessage::ROLE_ASSISTANT, ConversationMessage::ROLE_STAFF, ConversationMessage::ROLE_SYSTEM])
            ->orderBy('created_at')
            ->get()
            ->map(fn (ConversationMessage $m) => MessagePresenter::format($m))
            ->values();

        return response()->json([
            'messages' => $messages,
            'status' => $conversation->status,
        ]);
    }

    /**
     * The concierge's reply as audio. Only an assistant message of this guest's
     * own conversation can be spoken, so the endpoint is not a free voice for
     * arbitrary text. Any failure is a plain 204: the reply is already on
     * screen and the concierge simply stays quiet.
     */
    public function speech(SpeakMessageRequest $request, SpeechSynthesizer $speech): Response
    {
        $conversation = $this->findConversation(ResolveGym::gym($request), $request->validated('guest_token'));

        $message = $conversation->messages()
            ->where('role', ConversationMessage::ROLE_ASSISTANT)
            ->whereKey($request->validated('message_id'))
            ->firstOrFail();

        $text = SpeechText::from((string) $message->content);

        if ($text === '' || ! $speech->available()) {
            return response()->noContent();
        }

        try {
            $audio = $speech->synthesize($text);
        } catch (\Throwable $e) {
            Log::warning('Concierge speech failed', ['message_id' => $message->id, 'error' => $e->getMessage()]);

            return response()->noContent();
        }

        return response($audio, 200, [
            'Content-Type' => SpeechSynthesizer::MIME_TYPE,
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }

    private function findConversation(Gym $gym, string $guestToken): Conversation
    {
        $conversation = Conversation::where('gym_id', $gym->id)
            ->where('guest_token', $guestToken)
            ->first();

        if (! $conversation) {
            throw ValidationException::withMessages([
                'guest_token' => 'This conversation no longer exists. Please start a new one.',
            ]);
        }

        return $conversation;
    }
}
