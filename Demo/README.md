# Demo

This demo shows the main features of the library in a single runnable script.

## Run the demo

```bash
cd "C:\Users\Anthony\OneDrive\Modding\Composer Librarys\2FactorLibrary"
php Demo\demo.php
```

The demo will:

- generate a TOTP secret and code
- validate the code
- generate a QR code image
- create and validate a passkey-style credential
- generate recovery codes

The generated QR code is written to `Demo/output/totp-qr.png`.
