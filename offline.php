<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>You're Offline – GadgetZone</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{min-height:100vh;display:flex;align-items:center;justify-content:center;background:#f0f4f8;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;color:#333;}
        .offline-card{background:#fff;border-radius:20px;padding:60px 48px;text-align:center;max-width:480px;width:90%;box-shadow:0 8px 40px rgba(30,58,95,.12);}
        .offline-icon{font-size:80px;margin-bottom:24px;display:block;}
        h1{font-size:2rem;font-weight:800;color:#1e3a5f;margin-bottom:14px;}
        p{color:#666;font-size:1.05rem;line-height:1.7;margin-bottom:32px;}
        .retry-btn{display:inline-block;background:#ff6b35;color:#fff;border:none;padding:14px 40px;border-radius:10px;font-size:1rem;font-weight:700;cursor:pointer;text-decoration:none;transition:background .2s;}
        .retry-btn:hover{background:#e85d2c;}
        .brand{margin-top:32px;font-size:.9rem;color:#1e3a5f;font-weight:700;letter-spacing:.5px;}
        .brand span{color:#ff6b35;}
    </style>
</head>
<body>
    <div class="offline-card">
        <span class="offline-icon">📡</span>
        <h1>You're Offline</h1>
        <p>Please check your internet connection and try again. Some content may still be available from your cache.</p>
        <button class="retry-btn" onclick="window.location.reload()">Try Again</button>
        <div class="brand">⚡ Gadget<span>Zone</span></div>
    </div>
</body>
</html>
