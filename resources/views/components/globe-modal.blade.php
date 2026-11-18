<!-- 3D Global Shipment Globe Modal (globe.gl) -->
<div id="globeModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Backdrop with blur -->
    <div class="fixed inset-0 bg-slate-950/85 backdrop-blur-md transition-opacity" onclick="closeGlobeModal()"></div>

    <!-- Modal Dialog Window -->
    <div class="flex min-h-screen items-center justify-center p-2 sm:p-4 text-center">
        <div class="relative transform overflow-hidden rounded-2xl bg-[#090E20] border border-slate-700/80 text-left shadow-2xl transition-all w-full max-w-5xl flex flex-col h-[90vh] max-h-[780px]" onclick="event.stopPropagation()">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-800 bg-[#0B132B]/90 backdrop-blur-sm z-10">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-sky-500/10 border border-sky-500/30 flex items-center justify-center text-sky-400 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-white flex items-center gap-2" id="modal-title">
                            3D Global Shipment Globe
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-sky-950 text-sky-300 border border-sky-800">
                                Live Interactive
                            </span>
                        </h3>
                        <p class="text-xs text-slate-400">
                            Visualisasi bola dunia 3D interaktif rute maritim & kargo global aktif dari database
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <span class="hidden md:inline-flex items-center gap-1.5 text-xs text-slate-300 bg-slate-800/80 px-2.5 py-1 rounded-lg border border-slate-700">
                        <svg class="w-3.5 h-3.5 text-sky-400 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Drag untuk putar • Scroll untuk zoom
                    </span>
                    <!-- Close Button (X) -->
                    <button type="button" onclick="closeGlobeModal()" class="rounded-lg p-1.5 text-slate-400 hover:text-white hover:bg-slate-800 transition focus:outline-none" aria-label="Tutup">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Modal Body (3D Globe Canvas Container) -->
            <div class="relative flex-1 w-full bg-[#070D1E] overflow-hidden">
                <div id="modalGlobeCanvas" class="w-full h-full"></div>

                <!-- Floating Overlay Controls inside Globe -->
                <div class="absolute top-3 left-3 z-10 flex flex-col gap-2 pointer-events-auto">
                    <!-- Status Legend -->
                    <div class="bg-slate-900/85 backdrop-blur-sm text-slate-200 px-3 py-2 rounded-xl text-[11px] font-medium border border-slate-700 shadow-lg flex flex-col gap-1.5">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Status Rute Aktif</span>
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-sky-400 ring-2 ring-sky-400/30"></span> In Transit
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-500 ring-2 ring-rose-500/30"></span> Delayed
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 ring-2 ring-emerald-400/30"></span> Delivered
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-400 ring-2 ring-indigo-400/30"></span> Booked
                        </div>
                    </div>
                </div>

                <div class="absolute top-3 right-3 z-10 flex items-center gap-2 pointer-events-auto">
                    <button type="button" id="btnModalResetGlobe" class="px-2.5 py-1.5 rounded-lg bg-slate-900/80 hover:bg-slate-800 text-slate-200 text-xs font-medium border border-slate-700 shadow backdrop-blur-sm transition flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Reset Kamera
                    </button>
                    <button type="button" id="btnModalToggleRotate" class="px-2.5 py-1.5 rounded-lg bg-slate-900/80 hover:bg-slate-800 text-slate-200 text-xs font-medium border border-slate-700 shadow backdrop-blur-sm transition flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/></svg>
                        <span id="labelAutoRotate">Auto-Rotate</span>
                    </button>
                </div>

                <!-- Loading State inside Globe -->
                <div id="modalGlobeLoading" class="absolute inset-0 flex flex-col items-center justify-center bg-[#070D1E] z-20 transition-opacity">
                    <div class="w-10 h-10 border-4 border-sky-500/20 border-t-sky-500 rounded-full animate-spin mb-3"></div>
                    <p class="text-xs text-sky-300 font-medium tracking-wide">Memuat Visualisasi 3D Globe...</p>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 px-5 py-3 border-t border-slate-800 bg-[#0B132B] z-10">
                <div class="flex items-center gap-2 text-xs text-slate-400">
                    <svg class="w-4 h-4 text-sky-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span id="modalGlobeRouteCount">Sinkronisasi data rute pengiriman aktif...</span>
                </div>

                <div class="flex items-center gap-2.5 w-full sm:w-auto justify-end">
                    <button type="button" onclick="closeGlobeModal()" class="px-4 py-2 rounded-lg text-xs font-semibold bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700 transition">
                        Tutup
                    </button>
                    <!-- Tombol "Lihat Detail di Dashboard" -->
                    <a href="{{ route('dashboard') }}#global-map" onclick="closeGlobeModal()" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-bold bg-sky-600 hover:bg-sky-500 text-white shadow-md shadow-sky-600/20 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        Lihat Detail di Dashboard
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    (function () {
        let modalGlobeInstance = null;
        let modalAutoRotate = true;

        window.openGlobeModal = function () {
            const modal = document.getElementById('globeModal');
            if (!modal) return;
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');

            setTimeout(initOrUpdateModalGlobe, 80);
        };

        window.closeGlobeModal = function () {
            const modal = document.getElementById('globeModal');
            if (!modal) return;
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        };

        // Close on Escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                const modal = document.getElementById('globeModal');
                if (modal && !modal.classList.contains('hidden')) {
                    closeGlobeModal();
                }
            }
        });

        async function fetchGlobeShipments() {
            if (window.cargoTraceGlobalShipments && Array.isArray(window.cargoTraceGlobalShipments) && window.cargoTraceGlobalShipments.length > 0) {
                return window.cargoTraceGlobalShipments;
            }
            try {
                const res = await fetch("{{ route('global.globe-shipments') }}", {
                    headers: { 'Accept': 'application/json' }
                });
                if (res.ok) {
                    const data = await res.json();
                    window.cargoTraceGlobalShipments = data;
                    return data;
                }
            } catch (err) {
                console.warn('Gagal mengambil data rute global untuk globe:', err);
            }
            return [];
        }

        async function initOrUpdateModalGlobe() {
            const container = document.getElementById('modalGlobeCanvas');
            const loading = document.getElementById('modalGlobeLoading');
            const countLabel = document.getElementById('modalGlobeRouteCount');
            if (!container) return;

            if (typeof Globe === 'undefined') {
                console.warn('Globe.gl belum ter-load dari CDN.');
                return;
            }

            const width = container.clientWidth || 850;
            const height = container.clientHeight || 520;

            if (modalGlobeInstance) {
                modalGlobeInstance.width(width).height(height);
                if (loading) loading.classList.add('hidden');
                return;
            }

            const rawShipments = await fetchGlobeShipments();
            const shipments = Array.isArray(rawShipments) ? rawShipments : [];

            if (countLabel) {
                countLabel.textContent = `Visualisasi ${shipments.length} rute pengiriman aktif (${shipments.filter(s => s.status === 'IN_TRANSIT').length} In Transit, ${shipments.filter(s => s.is_delayed).length} Delayed).`;
            }

            const globeArcs = [];
            const globePoints = [];
            const seenPoints = new Set();

            const colorMap = {
                'IN_TRANSIT': '#0ea5e9',
                'PICKED_UP': '#38bdf8',
                'DELAYED': '#ef4444',
                'DELIVERED': '#10b981',
                'BOOKED': '#818cf8',
                'CREATED': '#94a3b8'
            };

            shipments.forEach(function (s) {
                if (s.origin && s.destination && s.origin.lat && s.destination.lat) {
                    const statusKey = s.is_delayed ? 'DELAYED' : (s.status || 'IN_TRANSIT');
                    const arcColor = colorMap[statusKey] || '#0ea5e9';

                    globeArcs.push({
                        startLat: s.origin.lat,
                        startLng: s.origin.lng,
                        endLat: s.destination.lat,
                        endLng: s.destination.lng,
                        color: arcColor,
                        label: `
                            <div style="background:#0b132b; color:#fff; border:1px solid #1e293b; padding:8px 12px; border-radius:8px; font-family:sans-serif; font-size:12px; box-shadow:0 10px 15px -3px rgba(0,0,0,0.5);">
                                <div style="font-weight:bold; color:#38bdf8; margin-bottom:4px; font-size:13px;">🚢 ${s.shipment_number}</div>
                                <div style="margin-bottom:2px;"><strong style="color:#94a3b8;">Rute:</strong> ${s.origin.city} (${s.origin.code}) &rarr; ${s.destination.city} (${s.destination.code})</div>
                                <div style="margin-bottom:2px;"><strong style="color:#94a3b8;">Carrier:</strong> ${s.carrier_name}</div>
                                <div><strong style="color:#94a3b8;">Status:</strong> <span style="color:${arcColor}; font-weight:bold;">${s.status_label || s.status}</span></div>
                            </div>
                        `
                    });

                    const oKey = `${s.origin.lat},${s.origin.lng}`;
                    if (!seenPoints.has(oKey)) {
                        seenPoints.add(oKey);
                        globePoints.push({
                            lat: s.origin.lat,
                            lng: s.origin.lng,
                            size: 0.28,
                            color: '#38bdf8',
                            label: `<div style="background:#0f172a; color:#38bdf8; border:1px solid #0284c7; padding:4px 8px; border-radius:6px; font-size:11px; font-weight:bold;">⚓ ${s.origin.city} (${s.origin.name})</div>`
                        });
                    }

                    const dKey = `${s.destination.lat},${s.destination.lng}`;
                    if (!seenPoints.has(dKey)) {
                        seenPoints.add(dKey);
                        globePoints.push({
                            lat: s.destination.lat,
                            lng: s.destination.lng,
                            size: 0.28,
                            color: '#10b981',
                            label: `<div style="background:#0f172a; color:#10b981; border:1px solid #059669; padding:4px 8px; border-radius:6px; font-size:11px; font-weight:bold;">🏁 ${s.destination.city} (${s.destination.name})</div>`
                        });
                    }
                }
            });

            modalGlobeInstance = Globe()
                (container)
                .backgroundColor('#070D1E')
                .globeImageUrl('https://unpkg.com/three-globe/example/img/earth-blue-marble.jpg')
                .bumpImageUrl('https://unpkg.com/three-globe/example/img/earth-topology.png')
                .showAtmosphere(true)
                .atmosphereColor('#38bdf8')
                .atmosphereAltitude(0.25)
                .pointsData(globePoints)
                .pointLat('lat')
                .pointLng('lng')
                .pointColor('color')
                .pointAltitude(0.015)
                .pointRadius('size')
                .pointLabel('label')
                .arcsData(globeArcs)
                .arcStartLat('startLat')
                .arcStartLng('startLng')
                .arcEndLat('endLat')
                .arcEndLng('endLng')
                .arcColor('color')
                .arcAltitude(0.25)
                .arcStroke(1.6)
                .arcDashLength(0.5)
                .arcDashGap(0.2)
                .arcDashAnimateTime(1800)
                .arcLabel('label')
                .width(width)
                .height(height);

            modalGlobeInstance.controls().autoRotate = true;
            modalGlobeInstance.controls().autoRotateSpeed = 0.6;
            modalGlobeInstance.pointOfView({ lat: 15, lng: 110, altitude: 2.2 }, 800);

            if (loading) loading.classList.add('hidden');

            const btnReset = document.getElementById('btnModalResetGlobe');
            if (btnReset) {
                btnReset.onclick = function () {
                    if (modalGlobeInstance) {
                        modalGlobeInstance.pointOfView({ lat: 15, lng: 110, altitude: 2.2 }, 800);
                    }
                };
            }

            const btnToggleRotate = document.getElementById('btnModalToggleRotate');
            if (btnToggleRotate) {
                btnToggleRotate.onclick = function () {
                    if (modalGlobeInstance) {
                        modalAutoRotate = !modalAutoRotate;
                        modalGlobeInstance.controls().autoRotate = modalAutoRotate;
                        btnToggleRotate.classList.toggle('text-emerald-400', modalAutoRotate);
                        btnToggleRotate.classList.toggle('text-slate-400', !modalAutoRotate);
                    }
                };
            }
        }

        window.addEventListener('resize', function () {
            const modal = document.getElementById('globeModal');
            const container = document.getElementById('modalGlobeCanvas');
            if (modalGlobeInstance && modal && !modal.classList.contains('hidden') && container) {
                modalGlobeInstance.width(container.clientWidth).height(container.clientHeight);
            }
        });
    })();
</script>
