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
                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            autocomplete="current-password"
                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 outline-none"
                        >
                        <p id="password-error" class="mt-1 text-xs text-red-500" hidden></p>
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
                    }
                }

                email.addEventListener('input', onInput);
                password.addEventListener('input', onInput);
            })();
        </script>
    </body>
</html>
