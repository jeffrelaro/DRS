<?php
/**
 * Small inline SVG icons for the form fields.
 * Usage in a page: echo icon('mail');  or  echo icon('phonemail', 'field-icon--wide');
 */
function icon(string $name, string $extra = ''): string
{
    $icons = [
        'mail' => '<rect x="4" y="8" width="24" height="16" rx="2"/><path d="M4.5 9.5l11.5 9 11.5-9"/>',
        'user' => '<circle cx="16" cy="11" r="5"/><path d="M6 27c0-5.5 4.5-9 10-9s10 3.5 10 9"/>',
        'phone' => '<g transform="translate(2 2) scale(1.2)"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></g>',
        'lock' => '<rect x="7" y="14" width="18" height="13" rx="2"/><path d="M11 14v-4a5 5 0 0 1 10 0v4"/><circle cx="16" cy="20.5" r="1.4"/>',
        'phonemail' => '<rect x="10" y="5" width="18" height="13" rx="1.6"/><path d="M10.5 6.5l8.5 6.5 8.5-6.5"/><g transform="translate(3 11) scale(0.82)" fill="#d9d9d9"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></g>',
    ];

    $inner = $icons[$name] ?? '';
    $class = trim('field-icon ' . $extra);

    return '<svg class="' . $class . '" aria-hidden="true" focusable="false" viewBox="0 0 32 32" '
         . 'fill="none" stroke="#555" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">'
         . $inner . '</svg>';
}
