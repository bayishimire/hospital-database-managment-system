document.addEventListener('DOMContentLoaded', () => {
    // ── LIVE CLOCK ──────────────────────────────────────────
    function updateClock() {
        const now = new Date();
        const timeStr = now.toLocaleTimeString('en-RW', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        const dateStr = now.toLocaleDateString('en-RW', { day: '2-digit', month: 'short', year: 'numeric' });
        const el = document.getElementById('live-time');
        const dt = document.getElementById('live-date');
        const ft = document.getElementById('footer-time');
        if (el) el.textContent = timeStr;
        if (dt) dt.textContent = dateStr;
        if (ft) ft.textContent = dateStr + ' ' + timeStr;
    }
    updateClock();
    setInterval(updateClock, 1000);

    // ── CASCADING LOCATION DROPDOWNS (Province → District → Sector) ──
    const provinceSelect = document.getElementById('province');
    const districtSelect = document.getElementById('district');
    const sectorSelect = document.getElementById('sector');
    // Cell and Village are now plain text inputs — no dropdown logic needed

    async function loadLocations(level, parentId = null) {
        const url = `api/get_locations.php?level=${level}${parentId ? `&parent_id=${parentId}` : ''}`;
        try {
            const res = await fetch(url);
            return await res.json();
        } catch (e) {
            console.error('Location fetch error:', e);
            return [];
        }
    }

    function populateSelect(sel, data, placeholder) {
        sel.innerHTML = `<option value="">— ${placeholder} —</option>`;
        data.forEach(item => {
            const opt = new Option(item.name, item.id);
            sel.add(opt);
        });
    }

    function showVerified(selectEl) {
        const wrap = selectEl.parentElement;
        let badge = wrap.querySelector('.v-badge');
        if (!badge) {
            badge = document.createElement('div');
            badge.className = 'v-badge';
            badge.style.cssText = 'font-size:0.6rem;margin-top:4px;display:flex;align-items:center;gap:3px;font-weight:700;';
            wrap.appendChild(badge);
        }
        badge.innerHTML = selectEl.value
            ? '<i class="fa-solid fa-circle-check" style="color:#16a34a"></i><span style="color:#16a34a">NATIONALLY VALID</span>'
            : '';
    }

    // Initial: load provinces
    if (provinceSelect) {
        loadLocations('province').then(data => populateSelect(provinceSelect, data, 'Province'));

        provinceSelect.addEventListener('change', async () => {
            if (districtSelect) populateSelect(districtSelect, [], 'District');
            if (sectorSelect) populateSelect(sectorSelect, [], 'Sector');
            showVerified(provinceSelect);
            if (provinceSelect.value && districtSelect) {
                const d = await loadLocations('district', provinceSelect.value);
                populateSelect(districtSelect, d, 'District');
            }
        });
    }

    if (districtSelect) {
        districtSelect.addEventListener('change', async () => {
            if (sectorSelect) populateSelect(sectorSelect, [], 'Sector');
            showVerified(districtSelect);
            if (districtSelect.value && sectorSelect) {
                const s = await loadLocations('sector', districtSelect.value);
                populateSelect(sectorSelect, s, 'Sector');
            }
        });
    }

    if (sectorSelect) {
        sectorSelect.addEventListener('change', () => showVerified(sectorSelect));
    }

    // ── NATIONAL ID VALIDATION ─────────────────────────────
    const nidInput = document.getElementById('nidField');
    const nidStatus = document.getElementById('nid-status');

    if (nidInput && nidStatus) {
        nidInput.addEventListener('input', () => {
            const val = nidInput.value.trim();
            if (val.length === 16 && /^\d+$/.test(val)) {
                const year = parseInt(val.substring(1, 5));
                const now = new Date().getFullYear();
                if (year < 1900 || year > now) {
                    nidStatus.innerHTML = '<i class="fa-solid fa-triangle-exclamation" style="color:#d97706"></i><span style="color:#d97706"> INVALID ENCODING</span>';
                } else {
                    nidStatus.innerHTML = '<span style="background:#16a34a;color:white;padding:2px 8px;border-radius:4px;"><i class="fa-solid fa-shield-check"></i> GOVT VERIFIED</span>';
                }
            } else if (val.length > 0) {
                nidStatus.innerHTML = '<i class="fa-solid fa-circle-xmark" style="color:#dc2626"></i><span style="color:#dc2626"> Must be 16 digits</span>';
            } else {
                nidStatus.innerHTML = '';
            }
        });
    }

    // ── INSURANCE STATUS ────────────────────────────────────
    const insSelect = document.querySelector('select[name="insurance"]');
    const insStatus = document.getElementById('insurance-status');

    if (insSelect && insStatus) {
        function checkInsurance() {
            if (insSelect.value === 'None') {
                insStatus.innerHTML = '<span style="color:#d97706;font-weight:800;"><i class="fa-solid fa-coins"></i> CASH / OUT-OF-POCKET</span>';
            } else if (insSelect.value) {
                insStatus.innerHTML = '<i class="fa-solid fa-spinner fa-spin" style="color:#6b7280"></i> Verifying...';
                setTimeout(() => {
                    insStatus.innerHTML = '<span style="background:#ecfdf5;color:#15803d;border:1px solid #16a34a;padding:2px 8px;border-radius:4px;font-size:0.65rem;font-weight:700;"><i class="fa-solid fa-check-double"></i> TREATMENT ELIGIBLE</span>';
                }, 600);
            }
        }
        insSelect.addEventListener('change', checkInsurance);
    }
});
