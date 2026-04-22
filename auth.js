// Signup form validation

function checkAlpha(id, errId) {
    const el = document.getElementById(id);
    const err = document.getElementById(errId);
    if (!el || !err) return;
    el.addEventListener('input', () => {
        const val = el.value;
        const ok = /^[a-zA-Z ]*$/.test(val);
        err.textContent = ok ? '' : 'Only letters are allowed';
    });
}

checkAlpha('fname', 'efname');
checkAlpha('lname', 'elname');

// Email
const emailEl = document.getElementById('email');
const emailErr = document.getElementById('eemail');
if (emailEl) {
    emailEl.addEventListener('input', () => {
        const val = emailEl.value;
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!val) { emailErr.textContent = ''; return; }
        emailErr.textContent = re.test(val) ? '' : 'Invalid email format';
    });
}

// Password strength
const passEl = document.getElementById('pass');
const hints = document.getElementById('passHints');
if (passEl && hints) {
    passEl.addEventListener('input', () => {
        const val = passEl.value;
        let html = '';
        const checks = [
            { label: 'At least 5 characters', ok: val.length >= 5 },
            { label: 'Uppercase letter', ok: /[A-Z]/.test(val) },
            { label: 'Lowercase letter', ok: /[a-z]/.test(val) },
            { label: 'Number', ok: /[0-9]/.test(val) },
            { label: 'Special character', ok: /[^a-zA-Z0-9 ]/.test(val) },
        ];
        checks.forEach(c => {
            html += `<span style="color:${c.ok ? '#4ade80' : '#f87171'};margin-right:12px">${c.ok ? '✔' : '✘'} ${c.label}</span>`;
        });
        hints.innerHTML = html;
    });
}

// Confirm password
const cpassEl = document.getElementById('cpass');
const cpassErr = document.getElementById('ecpass');
if (cpassEl && cpassErr) {
    cpassEl.addEventListener('input', () => {
        const pass = passEl ? passEl.value : '';
        if (!cpassEl.value) { cpassErr.textContent = ''; return; }
        if (cpassEl.value === pass) {
            cpassErr.textContent = '✔ Passwords match';
            cpassErr.style.color = '#4ade80';
        } else {
            cpassErr.textContent = '✘ Passwords do not match';
            cpassErr.style.color = '#f87171';
        }
    });
}
