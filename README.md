# Fitness Gym AI

Pengalaman gym interaktif dengan AI Fitness Concierge, dashboard charcoal–lime, ilustrasi gym, narator, chat, dan tiga bahasa (Indonesia, English, 日本語).

Enam program demo: Strength Foundations, Cardio Circuit, Yoga Flow, Personal Training, Functional Training, dan Mobility Reset. Setiap studio terhubung ke program; harga bersumber dari katalog program.

## Booking

Satu permintaan untuk satu peserta dan sesi 60 menit. Slot tersedia hingga 60 hari ke depan. Permintaan menahan satu tempat dengan status `pending`; staf mengonfirmasi atau membatalkan. Pembatalan mengembalikan satu tempat sekali dan booking batal tidak dapat dibuka kembali. Nama program, studio, harga, dan durasi disimpan saat booking.

Admin mengelola program, studio, kapasitas dan jadwal harian, knowledge, booking, serta handover percakapan. Membership berupa informasi dan kontak staf; penjualan/perpanjangan membership serta pembayaran belum tersedia. Semua harga, kebijakan, dan lokasi demo diberi label contoh.

## Lokal

PHP 8.4+, Composer, Node >=22.12. SQLite untuk development dan tes.

```sh
composer setup
php artisan db:seed
php artisan serve
```

Pembuka `/`, dashboard `/fitness-gym-ai`, admin `/admin`. Akun demo hanya dibuat di local/testing. Untuk akun owner produksi gunakan prompt privat:

```sh
php artisan gym:create-admin --gym=fitness-gym-ai --email=admin@example.com --name=Admin
```

Password minimal 12 karakter, dimasukkan dua kali melalui prompt atau stdin. Jangan memakai UserSeeder di produksi.

## AI

Isi `.env` privat dari `.env.example`: `LOCAL_LLM_BASE_URL` (tanpa `/v1`), `LOCAL_LLM_API_KEY`, `LOCAL_LLM_MODEL`, `ASSISTANT_REASONING_EFFORT`. TTS opsional memakai `OPENROUTER_API_KEY`, `OPENROUTER_TTS_MODEL`, dan `OPENROUTER_TTS_VOICE`. AI mengakses tujuh tool tenant-scoped: `search_knowledge`, `search_program`, `get_fitness_program`, `search_training`, `check_session_availability`, `create_reservation_request`, dan `request_human_handover`.

AI hanya menjelaskan informasi gym yang tersedia, tidak memberikan diagnosis atau rehabilitasi medis, tidak mengonfirmasi booking, dan tidak mengambil pembayaran.

## Pemeriksaan

```sh
php artisan test --compact
npm run test:ui
npm run lint:ui
npm run build
php artisan view:cache
php vendor/bin/pint --dirty --format agent
composer audit
npm audit --omit=dev
```

CI membangun aset sebelum tes halaman. CI dan webhook Plesk berjalan terpisah; webhook push tidak menunggu CI.

## Plesk

Target: `https://fitness-gym-ai.fts-tech.co.id`, repository `https://github.com/Rizz404/fitness-gym-ai`, branch `main`. Produksi memakai PostgreSQL, PHP 8.4, Node 24, document root `public/`. Template environment ada di `deploy/env.production.example`; rahasia hanya di `.env` atau direktori privat server.

Laravel Toolkit memasang dependency. Additional deployment actions:

```sh
set -e
export PATH=/opt/plesk/node/24/bin:$PATH
npm run build
/opt/plesk/php/8.4/bin/php artisan migrate --force --no-interaction
/opt/plesk/php/8.4/bin/php artisan optimize
```

PHP memory 256M, execution 95 detik, Apache ProxyTimeout 100. Timestamp validation OPcache tetap aktif. Isi `TRUSTED_PROXIES` hanya dengan proxy aktual. Template nginx/supervisor adalah referensi untuk VPS mandiri, bukan pengganti konfigurasi Plesk.

Untuk tenant kosong, jalankan `ProductionGymSeeder`. Untuk demo produksi, jalankan `GymSeeder`, `FitnessProgramSeeder`, `TrainingAreaSeeder`, `TrainingImageSeeder`, `SessionInventorySeeder`, `GymKnowledgeItemSeeder` secara terpisah sebagai pengguna aplikasi. Jangan jalankan UserSeeder/GymUserSeeder di produksi. Seeder tenant idempoten tidak menimpa tenant yang sudah ada.

Aktifkan scheduler Toolkit untuk `gym:open-inventory`, dijadwalkan harian 01.00 WIB. Command memperpanjang jadwal tanpa mereset tempat yang sudah dipesan. Mailbox/kontak demo `fitness-gym-ai@fts-tech.co.id`; SMTP tetap `log` sampai dikonfigurasi khusus. Health check `/up`.

## Aset

`public/images/gym/interior.webp`: interior gym charcoal dengan aksen lime, ilustrasi arsitektur lebar tanpa orang dan teks. `concierge.webp`: karakter concierge berbusana charcoal–lime, tablet, latar transparan. Aset dibuat dengan ImageGen dan disimpan di repository.
