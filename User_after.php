# Modul 4 — Voucher & Promo System

## Masalah yang Ditemukan

| No | Masalah | Lokasi |
|----|---------|--------|
| 1 | Race condition — dua request bersamaan bisa double redemption | `VoucherController::redeem()` |
| 2 | Tidak ada idempotency — client retry menyebabkan double redeem | `VoucherController::redeem()` |
| 3 | Tidak ada validasi domain — expired, min purchase tidak dicek | `VoucherController::redeem()` |
| 4 | Primitive Obsession — kode voucher dan tipe diskon string bebas | `Voucher.php` |
| 5 | `usage_count` bisa diubah langsung dari luar | `Voucher.php` |
| 6 | Enumeration attack — `checkUsage()` mengekspos data internal | `VoucherController::checkUsage()` |

## Konsep yang Diterapkan

- **Idempotency Key** — request yang sama dikirim dua kali tidak double redeem
- **Pessimistic Locking** — `lockForUpdate()` mencegah race condition
- **Value Object** — `VoucherCode` dan `Discount` menghilangkan Primitive Obsession
- **Domain Validation** — `canBeRedeemed()` mengecek semua kondisi sebelum eksekusi
- **Enumeration Attack Prevention** — `checkUsage()` dihapus total

## File

| File | Keterangan |
|------|------------|
| `VoucherController_before.php` | Kode bermasalah |
| `Voucher_before.php` | Model sebelum perbaikan |
| `ValueObjects/VoucherCode.php` | Value Object kode voucher |
| `ValueObjects/Discount.php` | Value Object diskon |
| `Voucher_after.php` | Model dengan validasi domain + locking |
| `VoucherController_after.php` | Controller tipis setelah perbaikan |
