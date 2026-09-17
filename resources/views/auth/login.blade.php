<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — TaskFlow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #0A1929; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .auth-wrapper { width: 100%; max-width: 440px; padding: 20px; }
        .auth-brand { text-align: center; margin-bottom: 32px; }
        .auth-brand .brand-icon { width: 56px; height: 56px; background: #0052CC; border-radius: 14px; display: inline-flex; align-items: center; justify-content: center; font-size: 28px; color: #fff; margin-bottom: 12px; }
        .auth-brand h1 { font-size: 26px; font-weight: 700; color: #fff; margin: 0; }
        .auth-brand p { color: #B0BEC5; font-size: 14px; margin-top: 4px; }
        .auth-card { background: #fff; border-radius: 14px; padding: 36px 40px; box-shadow: 0 20px 60px rgba(0,0,0,.4); }
        .auth-card h2 { font-size: 20px; font-weight: 700; color: #172B4D; margin-bottom: 6px; }
        .auth-card .subtitle { font-size: 14px; color: #6B778C; margin-bottom: 28px; }
        .form-label { font-size: 13px; font-weight: 600; color: #172B4D; }
        .form-control { border: 1.5px solid #DFE1E6; border-radius: 8px; padding: 10px 14px; font-size: 14px; color: #172B4D; transition: border-color .2s; }
        .form-control:focus { border-color: #0052CC; box-shadow: 0 0 0 3px rgba(0,82,204,.15); }
        .btn-login { background: #0052CC; color: #fff; border: none; width: 100%; padding: 11px; border-radius: 8px; font-size: 15px; font-weight: 600; transition: background .2s; }
        .btn-login:hover { background: #0065FF; }
        .divider { text-align: center; color: #6B778C; font-size: 13px; margin: 20px 0; position: relative; }
        .divider::before, .divider::after { content: ''; position: absolute; top: 50%; width: 40%; height: 1px; background: #DFE1E6; }
        .divider::before { left: 0; } .divider::after { right: 0; }
        .alert { border-radius: 8px; border: none; font-size: 13.5px; }
        .alert-danger { background: #FFEBE6; color: #BF2600; }
        .input-group-text { border: 1.5px solid #DFE1E6; background: #F4F5F7; cursor: pointer; }
        .demo-box { background: #F4F5F7; border-radius: 8px; padding: 12px 16px; margin-bottom: 20px; font-size: 12.5px; color: #172B4D; }
        .demo-box strong { color: #0052CC; }
    </style>
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-brand">
        <div class="brand-icon"><i class="bi bi-kanban-fill"></i></div>
        <h1>TaskFlow</h1>
        <p>Task management</p>
    </div>

    <div class="auth-card">
        <h2>Welcome back</h2>
        <p class="subtitle">Sign in to your account to continue</p>

        @if($errors->any())
            <div class="alert alert-danger mb-3">
                <i class="bi bi-exclamation-circle me-2"></i>
                {{ $errors->first() }}
            </div>
        @endif

        {{-- <div class="demo-box">
            <i class="bi bi-info-circle me-1"></i> Demo accounts (password: <strong>password</strong>)<br>
            <strong style="color:#0052CC;">Admin:</strong> admin@taskflow.com &nbsp;·&nbsp;
            <strong style="color:#36B37E;">Employee:</strong> employee@taskflow.com &nbsp;·&nbsp;
            <strong style="color:#6554C0;">Client:</strong> client@taskflow.com
        </div> --}}

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Email address</label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}" placeholder="you@company.com" required autofocus>
            </div>

            <div class="mb-4">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <input type="password" name="password" id="passwordField"
                           class="form-control @error('password') is-invalid @enderror"
                           placeholder="Enter your password" required>
                    <span class="input-group-text border-start-0" onclick="togglePwd()">
                        <i class="bi bi-eye" id="eyeIcon" style="font-size:15px;color:#6B778C;"></i>
                    </span>
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="form-check mb-0">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label" for="remember" style="font-size:13px;">Remember me</label>
                </div>
                @if(Route::has('password.request'))
                <a href="{{ route('password.request') }}" style="font-size:13px;color:#0052CC;">Forgot password?</a>
                @endif
            </div>

            <button type="submit" class="btn btn-login">
                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
            </button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function togglePwd() {
        const f = document.getElementById('passwordField');
        const i = document.getElementById('eyeIcon');
        if (f.type === 'password') { f.type = 'text'; i.className = 'bi bi-eye-slash'; }
        else { f.type = 'password'; i.className = 'bi bi-eye'; }
    }
</script>
</body>
</html>
