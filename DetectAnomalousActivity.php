# Modul 3 — E-Wallet System

## Masalah yang Ditemukan

| No | Masalah | Lokasi |
|----|---------|--------|
| 1 | Saldo bisa negatif — tidak ada pengecekan sebelum withdraw | `WalletController::withdraw()` |
| 2 | Tidak ada daily limit | `WalletController::withdraw()` |
| 3 | Race condition — dua request bersamaan bisa double spending | `WalletController::withdraw()` |
| 4 | Transfer tidak atomic — uang bisa hilang jika gagal di tengah | `WalletController::transfer()` |
| 5 | God Object — notifikasi, report, fraud detection di satu class | `Wallet.php`, `WalletController.php` |
| 6 | Tidak ada transaction log | Semua file |

## Konsep yang Diterapkan

- **Pessimistic Locking** — `lockForUpdate()` mencegah race condition di level DB
- **Atomic Transaction** — `DB::transaction()` memastikan tidak ada uang yang hilang
- **Daily Limit** — tracked via `daily_spent` yang di-reset tiap hari
- **Domain Service** — transfer dipindah ke `WalletTransferService`
- **Event-Driven Architecture** — anomaly detection via `DetectAnomalousActivity` listener

## File

| File | Keterangan |
|------|------------|
| `Wallet_before.php` | Kode bermasalah (God Object) |
| `WalletController_before.php` | Controller bermasalah |
| `Wallet_after.php` | Model dengan locking + daily limit |
| `WalletTransferService.php` | Domain Service untuk transfer |
| `WalletController_after.php` | Controller tipis |
| `Events/` | Event classes |
| `Listeners/DetectAnomalousActivity.php` | Event listener fraud detection |
