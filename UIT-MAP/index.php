<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UIT-MAP Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://www.unpkg.com/@heyform-inc/embed@latest/dist/index.umd.js" defer></script>
    <?php include 'favicon.php' ?>
    <?php include 'theme.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --bg: #ffffff;
            --text: #0f172a;
            --primary: #3b82f6;
            --header-bg: #3b82f6;
            --nav-bg: #2563eb;
            --link-hover: #1d4ed8;
            --muted: #6b7280;
            --card-bg: #ffffff;
        }
        [data-theme="dark"] {
            --bg: #0b1220;
            --text: #e6eef8;
            --primary: #60a5fa;
            --header-bg: #071033;
            --nav-bg: #07203a;
            --link-hover: #93c5fd;
            --muted: #94a3b8;
            --card-bg: #071025;
        }

        body {
            background-image: url('img/uit.png');
            background-size: cover;
            background-repeat: no-repeat;
            background-color: var(--bg);
            color: var(--text);
            transition: background-color 200ms, color 200ms;
        }
        #feedback {
            position: fixed !important;
            bottom: 20px;
            right: 20px;
            padding: 8px 12px; /* Smaller padding */
            font-size: 14px;   /* Smaller font size */
            z-index: 9999;
            cursor: pointer;
        }
        div[data-heyform-id="Bio4zgP3"] .heyform__trigger-button:hover {
            background-color: #1e40af !important;  /* your hover color */
            opacity: 1 !important;
        }
        /* Links */
        a { transition: color 160ms ease, transform 120ms ease, opacity 120ms ease; }
        a:hover { color: var(--link-hover); transform: translateY(-2px); }
        a:active { transform: translateY(0); opacity: 0.85; }

        /* Card */
        .card { background-color: var(--card-bg) !important; }

        .theme-toggle { background: transparent; border: 1px solid rgba(0,0,0,0.08); padding: 6px 8px; border-radius: 6px; color: inherit; }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">

<div id="main-card" class="card p-8 w-full lg:w-1/3 bg-white rounded bg-opacity-80" role="main" aria-labelledby="login-heading" id="main">
    <div style="position: absolute; right: 24px; top: 24px;">
        <button id="theme-toggle" class="theme-toggle" aria-label="Toggle theme">🌙</button>
    </div>
<h2 class="text-2xl font-bold mb-4"><center>UIT-MAP: Monitoring and Assessment of Project</center></h2>
        <div class="flex justify-center mb-6">
                    <img src="img/COLLEGE_T.png" alt="United Institute of Technology logo" width="200" height="100" loading="lazy">
            </div>
            <h1 class="text-center text-2xl font-bold mb-4">Login Here</h1>
            <?php include 'csrf.php'; ?>
            <form action="login.php" method="POST">
            <?php echo csrf_input(); ?>
                <div class="mb-4">
                    <label for="uniqueId" class="block text-sm font-medium text-black-700"><b>Unique Id</b></label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <input type="text" id="uniqueId" name="uniqueId" pattern="[A-Za-z0-9]{4,20}" title="Please enter a valid unique ID (4-20 alphanumeric characters)" class="block w-full pr-10 border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-gray-400"></i>
                        </div>
                    </div>
                </div>
                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-black-700"><b>Password</b></label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <input type="password" name="password" id="password" class="block w-full pr-10 border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" aria-describedby="pw-strength" aria-label="Password">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer">
                        <i class="fas fa-eye text-gray-400" id="togglePassword" role="button" aria-label="Show or hide password" tabindex="0"></i>
                    </div>
                    <div id="pw-strength" class="mt-2 text-sm" aria-live="polite">Password strength: <span id="pw-strength-text">—</span></div>
                        <h4>
                        <!-- To print error message if the username or password is incorrect -->
                        <?php
                            session_start();
                            if (isset($_SESSION['loginMessage'])) {//This will execute only if there is any error message stored in session variable by the login page
                                echo '<span style="color: red;">' . $_SESSION['loginMessage'] . '</span>'; //Fetching the login error message from login page and displaying it here if incorrect username or password is entered
                            }
                            session_destroy();
                            if(isset($_SESSION['status'])){
                                echo "<p style='color: red;'>" . $_SESSION['status'] . "</p>";
                                unset($_SESSION['status']);
                            }
                        ?>
                        </h4>
                    </div>
                </div>
                <div class="flex items-center mb-4">
                    <input type="checkbox" id="remember" name="remember" class="mr-2" />
                    <label for="remember" class="text-sm">Remember me</label>
                </div>
                <div class="flex justify-center">
                    <button type="submit" name="submit" class="w-full py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">Login</button>
                </div>
                <div class="mt-4 text-center">
                    <a href="forgotpassword.php" class="text-blue-500 text-sm"><b>Forgot your password?</b></a>
                </div>
            </form>
            <div class="mt-4 text-xs text-center text-black-500">
                <p class="mt-2">
                        <b>Made with &#10084; by <a href="https://www.linkedin.com/in/hridyesh-gupta/" class="text-blue-500">Hridyesh</a> and team</b>
                </p>
            </div>
            <div
                data-heyform-id="Bio4zgP3"
                data-heyform-type="modal"
                data-heyform-custom-url="https://heyform.net/f/Bio4zgP3"
                data-heyform-size="large"
                data-heyform-open-trigger="click"
                data-heyform-open-delay="5"
                data-heyform-open-scroll-percent="30"
                data-heyform-trigger-background="#2563eb"
                data-heyform-trigger-text="Feedback please!!"
                data-heyform-hide-after-submit="true"
                data-heyform-auto-close="3"
                data-heyform-transparent-background="false">
                <button id="feedback" class="heyform__trigger-button py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" type="button" onclick="HeyForm.openModal('Bio4zgP3Modal')">Feedback please!!</button>
            </div>
            <div class="mt-2 text-xs text-center text-gray-500">
                <p class="text-sm">&copy; 2025 • UNITED INSTITUTE OF TECHNOLOGY</p>
            </div>
        </div>
    <script>
        const passwordField = document.getElementById('password');
        const togglePassword = document.getElementById('togglePassword');

        togglePassword.addEventListener('click', function () {
            // Toggle password visibility
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);

            // Toggle eye icon between open and closed
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    </script>
    <script>
        // Remember-me and inline validation
        (function(){
            const form = document.querySelector('form[action="login.php"]');
            const uid = document.getElementById('uniqueId');
            const remember = document.getElementById('remember');
            // Prefill from localStorage
            try{ const saved = localStorage.getItem('uit-remember'); if(saved && uid){ uid.value = saved; remember.checked = true; } }catch(e){}

            if(form){
                form.addEventListener('submit', function(e){
                    // Inline validation
                    if(uid && !/^[A-Za-z0-9]{4,20}$/.test(uid.value)){
                        e.preventDefault(); showToast('Enter a valid Unique ID (4-20 alphanumeric characters)','error',3000); uid.focus(); return false;
                    }
                    // Store remember-me
                    if(remember && uid){ if(remember.checked) localStorage.setItem('uit-remember', uid.value); else localStorage.removeItem('uit-remember'); }
                    return true;
                });
            }
        })();
    </script>
    <script>
        // Simple password strength estimator (client-side)
        (function(){
            const pw = document.getElementById('password');
            const out = document.getElementById('pw-strength-text');
            function score(s){
                if(!s) return 0;
                let points = 0;
                if(s.length >= 8) points += 1;
                if(/[A-Z]/.test(s)) points += 1;
                if(/[0-9]/.test(s)) points += 1;
                if(/[^A-Za-z0-9]/.test(s)) points += 1;
                if(s.length >= 12) points += 1;
                return points;
            }
            function textForScore(n){
                if(n <= 1) return 'Very weak';
                if(n === 2) return 'Weak';
                if(n === 3) return 'Moderate';
                if(n === 4) return 'Strong';
                return 'Very strong';
            }
            if(pw && out){
                pw.addEventListener('input', ()=>{
                    const s = score(pw.value);
                    out.textContent = textForScore(s);
                });
                // enable keyboard toggle on icon
                const toggle = document.getElementById('togglePassword');
                if(toggle){ toggle.addEventListener('keydown', e=>{ if(e.key==='Enter' || e.key===' ') toggle.click(); }); }
            }
        })();
    </script>
    <script>
        // Theme init for index page + dark-accent hero toggle
        (function(){
            const root = document.documentElement;
            const btn = document.getElementById('theme-toggle');
            const main = document.getElementById('main-card');
            const stored = localStorage.getItem('theme');
            const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            let theme = stored || (prefersDark ? 'dark' : 'light');
            function apply(t){
                root.setAttribute('data-theme', t);
                if(main){
                    if(t === 'dark') main.classList.add('dark-accent-hero');
                    else main.classList.remove('dark-accent-hero');
                }
                if (btn) btn.textContent = t === 'dark' ? '☀️' : '🌙';
            }
            apply(theme);
            if (btn) btn.addEventListener('click', () => {
                theme = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
                apply(theme);
                localStorage.setItem('theme', theme);
            });
        })();
    </script>
</body>
</html>