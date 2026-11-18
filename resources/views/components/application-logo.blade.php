<svg {{ $attributes }} viewBox="0 0 240 50" fill="none" xmlns="http://www.w3.org/2000/svg">
    <defs>
        <linearGradient id="ctGlobeGrad" x1="5" y1="5" x2="45" y2="45" gradientUnits="userSpaceOnUse">
            <stop offset="0%" stop-color="#4F46E5" />
            <stop offset="100%" stop-color="#0284C7" />
        </linearGradient>
        <linearGradient id="ctOrbitGrad" x1="4" y1="36" x2="48" y2="12" gradientUnits="userSpaceOnUse">
            <stop offset="0%" stop-color="#06B6D4" />
            <stop offset="100%" stop-color="#4F46E5" />
        </linearGradient>
    </defs>

    <!-- Globe Sphere & Grids -->
    <g transform="translate(2, 1)">
        <!-- Outer Circle / Sphere -->
        <circle cx="24" cy="24" r="21" stroke="url(#ctGlobeGrad)" stroke-width="2.5" fill="#F8FAFC" />
        <circle cx="24" cy="24" r="21" fill="url(#ctGlobeGrad)" fill-opacity="0.08" />

        <!-- Parallels / Latitudes -->
        <line x1="3" y1="24" x2="45" y2="24" stroke="#818CF8" stroke-width="1.2" stroke-dasharray="2 1.5" />
        <ellipse cx="24" cy="15" rx="18" ry="4" stroke="#94A3B8" stroke-width="1" stroke-dasharray="2 2" fill="none" />
        <ellipse cx="24" cy="33" rx="18" ry="4" stroke="#94A3B8" stroke-width="1" stroke-dasharray="2 2" fill="none" />

        <!-- Meridians / Longitudes -->
        <line x1="24" y1="3" x2="24" y2="45" stroke="#94A3B8" stroke-width="1.2" stroke-dasharray="2 1.5" />
        <ellipse cx="24" cy="24" rx="11" ry="21" stroke="#818CF8" stroke-width="1.4" fill="none" />

        <!-- Dynamic Logistic Trajectory Arc (Flight / Sea Route) -->
        <path d="M 4 36 C 8 10, 36 6, 46 16" stroke="url(#ctOrbitGrad)" stroke-width="2.8" stroke-linecap="round" fill="none" />
        <!-- Fast Arrow Head at Arc Tip -->
        <polygon points="46,12 48,19 41,18" fill="#0284C7" />

        <!-- Origin & Destination Route Points -->
        <circle cx="12" cy="31" r="2.8" fill="#4F46E5" />
        <circle cx="34" cy="18" r="2.8" fill="#0284C7" />
        <path d="M 12 31 Q 23 20 34 18" stroke="#4F46E5" stroke-width="1.5" stroke-dasharray="2 2" fill="none" />
    </g>

    <!-- CargoTrace Typography -->
    <g transform="translate(60, 0)">
        <text x="0" y="31" font-family="'Figtree', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif" font-weight="900" font-size="26" letter-spacing="-0.5px">
            <tspan fill="#0F172A">Cargo</tspan><tspan fill="#4F46E5">Trace</tspan>
        </text>
        <text x="1" y="44" font-family="'Figtree', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif" font-weight="700" font-size="8.5" fill="#64748B" letter-spacing="2.2px">
            GLOBAL LOGISTICS
        </text>
    </g>
</svg>
