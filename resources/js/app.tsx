import '../css/app.css';

import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.tsx');
        const load = pages[`./Pages/${name}.tsx`];

        if (!load) {
            throw new Error(`Page not found: ${name}`);
        }

        return load().then((module: any) => module.default);
    },
    setup({ el, App, props }) {
        if (!el) {
            return;
        }

        const root = createRoot(el);

        root.render(<App {...props} />);
    },
    progress: {
        color: '#4B5563',
    },
});