/**
 * Alpine data for the admin frame: the mobile program drawer.
 */
export function adminShell() {
    return {
        drawerOpen: false,

        openDrawer() {
            this.drawerOpen = true;
        },

        closeDrawer() {
            this.drawerOpen = false;
        },
    };
}
