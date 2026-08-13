<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <title>Login - {{ config('app.name', 'Laravel') }}</title>

        <meta name="theme-color" content="#f1f5f9">
        <link rel="icon" href="/icons/icon-192.png" sizes="192x192" type="image/png">

        @vite(['resources/css/app.css'])
    </head>
    <body class="min-h-screen bg-slate-100 text-slate-900">
        <div class="fixed inset-0 pointer-events-none overflow-hidden">
            <div class="absolute inset-0 bg-grid bg-grid-faded anim-grid-pan opacity-60"></div>
            <div class="absolute -top-32 -left-32 w-[36rem] h-[36rem] rounded-full bg-cyan-400/20 blur-3xl"></div>
            <div class="absolute top-1/3 -right-40 w-[32rem] h-[32rem] rounded-full bg-fuchsia-400/15 blur-3xl"></div>
            <div class="absolute bottom-0 left-1/3 w-[28rem] h-[28rem] rounded-full bg-indigo-400/15 blur-3xl"></div>
        </div>

        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="w-full max-w-md glass rounded-2xl border border-slate-200/70 shadow-2xl p-8">
                <div class="text-center mb-8">
                    <h1 class="text-2xl font-display font-bold gradient-brand-text">DATA AGENDA</h1>
                    <p class="mt-1 text-sm text-slate-500">Login sebagai Staff untuk melanjutkan</p>
                </div>

                @if ($errors->any())
                    <div class="mb-4 p-4 bg-red-50/80 border border-red-200 text-red-700 text-sm rounded-xl">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="/login" class="space-y-4" novalidate>
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="username"
                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 outline-none"
                        >
                        <p id="email-error" class="mt-1 text-xs text-red-500" hidden></p>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                        <div class="relative">
                            <input
                                id="password"
                                name="password"
                                type="password"
                                required
                                autocomplete="current-password"
                                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm pr-10 focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 outline-none"
                            >
                            <button
                                type="button"
                                id="toggle-password"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 transition-colors"
                                aria-label="Tampilkan password"
                                title="Tampilkan password"
                            >
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-5 h-5">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke-linecap="round" stroke-linejoin="round" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                            </button>
                        </div>
                        <p id="password-error" class="mt-1 text-xs text-red-500" hidden></p>
                        <div id="password-meter" hidden>
                            <div class="mt-2 flex items-center gap-2">
                                <div class="flex-1 h-2 rounded-full overflow-hidden bg-slate-200">
                                    <div id="password-meter-bar" class="h-full transition-all duration-300" style="width: 0%"></div>
                                </div>
                                <span id="password-strength-label" class="text-xs font-medium text-slate-500">Kosong</span>
                            </div>
                            <ul id="password-rules" class="mt-2 space-y-1"></ul>
                            <p id="password-missing" class="mt-2 text-xs font-medium text-amber-600" hidden></p>
                        </div>
                    </div>

                    <label class="flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" name="remember" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        Ingat saya
                    </label>

                    <button
                        type="submit"
                        class="w-full px-4 py-2.5 text-sm font-medium text-white gradient-brand rounded-xl shadow-lg shadow-indigo-500/25 hover:opacity-90 transition-opacity"
                    >
                        Login
                    </button>
                </form>
            </div>
        </div>

        <script>
            (function () {
                var form = document.querySelector('form');
                var email = document.getElementById('email');
                var password = document.getElementById('password');
                var emailError = document.getElementById('email-error');
                var passwordError = document.getElementById('password-error');
                var togglePassword = document.getElementById('toggle-password');
                var passwordHidden = true;
                var meter = document.getElementById('password-meter');
                var meterBar = document.getElementById('password-meter-bar');
                var strengthLabel = document.getElementById('password-strength-label');
                var rulesList = document.getElementById('password-rules');
                var missingText = document.getElementById('password-missing');

                var passwordRules = [
                    { label: 'Minimal 8 karakter', check: function (p) { return p.length >= 8; } },
                    { label: 'Minimal 1 huruf kapital', check: function (p) { return /[A-Z]/.test(p); } },
                    { label: 'Minimal 1 angka', check: function (p) { return /[0-9]/.test(p); } },
                    { label: 'Minimal 1 karakter unik (simbol)', check: function (p) { return /[^A-Za-z0-9]/.test(p); } },
                ];

                passwordRules.forEach(function (rule) {
                    var li = document.createElement('li');
                    li.className = 'flex items-center gap-2 text-xs text-slate-400';
                    li.innerHTML = '<span>○</span>' + rule.label;
                    rulesList.appendChild(li);
                });

                function strengthClass(met) {
                    if (met === 0) return 'bg-slate-300';
                    if (met <= 2) return 'bg-red-500';
                    if (met === 3) return 'bg-amber-500';
                    return 'bg-emerald-500';
                }

                function strengthTextClass(met) {
                    if (met === 0) return 'text-slate-500';
                    if (met <= 2) return 'text-red-500';
                    if (met === 3) return 'text-amber-500';
                    return 'text-emerald-500';
                }

                function updatePasswordMeter() {
                    var value = password.value;
                    if (!value) {
                        meter.hidden = true;
                        return;
                    }
                    meter.hidden = false;

                    var met = passwordRules.filter(function (rule) { return rule.check(value); }).length;
                    var missing = passwordRules.filter(function (rule) { return !rule.check(value); });

                    meterBar.style.width = (met / passwordRules.length * 100) + '%';
                    meterBar.className = 'h-full transition-all duration-300 ' + strengthClass(met);

                    strengthLabel.textContent = met === 0 ? 'Kosong' : met <= 2 ? 'Lemah' : met === 3 ? 'Sedang' : 'Kuat';
                    strengthLabel.className = 'text-xs font-medium ' + strengthTextClass(met);

                    Array.prototype.forEach.call(rulesList.children, function (li, index) {
                        var rule = passwordRules[index];
                        var ok = rule.check(value);
                        li.querySelector('span').textContent = ok ? '✓' : '○';
                        li.className = 'flex items-center gap-2 text-xs ' + (ok ? 'text-emerald-600' : 'text-red-500');
                    });

                    if (missing.length > 0) {
                        missingText.hidden = false;
                        missingText.textContent = 'Masih kurang: ' + missing.map(function (rule) { return rule.label; }).join(', ');
                    } else {
                        missingText.hidden = true;
                    }
                }

                togglePassword.addEventListener('click', function () {
                    passwordHidden = !passwordHidden;
                    password.type = passwordHidden ? 'password' : 'text';
                    togglePassword.setAttribute('aria-label', passwordHidden ? 'Tampilkan password' : 'Sembunyikan password');
                    togglePassword.setAttribute('title', passwordHidden ? 'Tampilkan password' : 'Sembunyikan password');
                    togglePassword.innerHTML = passwordHidden
                        ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-5 h-5"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="3"/></svg>'
                        : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-5 h-5"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19M14.12 14.12a3 3 0 1 1-4.24-4.24" stroke-linecap="round" stroke-linejoin="round"/><path d="M1 1l22 22" stroke-linecap="round" stroke-linejoin="round"/></svg>';
                });

                var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                function validateEmail() {
                    if (!email.value.trim()) return 'Email wajib diisi.';
                    if (!emailPattern.test(email.value)) return 'Format email tidak valid.';
                    return null;
                }

                function validatePassword() {
                    if (!password.value) return 'Password wajib diisi.';
                    return null;
                }

                function setError(input, errorEl, message) {
                    if (message) {
                        errorEl.textContent = message;
                        errorEl.hidden = false;
                        input.style.borderColor = '#ef4444';
                    } else {
                        errorEl.hidden = true;
                        input.style.borderColor = '';
                    }
                }

                form.addEventListener('submit', function (e) {
                    var emailMsg = validateEmail();
                    var passwordMsg = validatePassword();

                    setError(email, emailError, emailMsg);
                    setError(password, passwordError, passwordMsg);

                    if (emailMsg || passwordMsg) {
                        e.preventDefault();
                        if (emailMsg) {
                            email.focus();
                        } else {
                            password.focus();
                        }
                    }
                });

                function onInput(e) {
                    if (e.currentTarget === email) {
                        setError(email, emailError, validateEmail());
                    } else {
                        setError(password, passwordError, validatePassword());
                        updatePasswordMeter();
                    }
                }

                email.addEventListener('input', onInput);
                password.addEventListener('input', onInput);
            })();
        </script>
    </body>
</html>
