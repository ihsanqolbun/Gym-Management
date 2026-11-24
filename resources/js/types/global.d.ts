// Ziggy menyuntik route() sebagai fungsi global lewat @routes di Blade
declare function route(): { current: (name: string) => boolean };
declare function route(name: string, params?: unknown, absolute?: boolean): string;