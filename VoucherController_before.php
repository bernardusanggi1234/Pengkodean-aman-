# Modul 2 — Order & Refund System

## Masalah yang Ditemukan

| No | Masalah | Lokasi |
|----|---------|--------|
| 1 | Status string bebas → typo "payed" bisa masuk database | `Order.php` |
| 2 | Boolean flag hell → 5 boolean bisa kombinasi tidak valid | `Order.php` |
| 3 | Tidak ada validasi urutan status → bisa ship sebelum paid | `OrderController.php` |
| 4 | Amount bisa diubah setelah order dibuat bahkan setelah paid | `OrderController::updateAmount()` |
| 5 | Amount bisa negatif | `OrderController::create()` |
| 6 | Refund bisa di-approve tanpa request terlebih dahulu | `OrderController::approveRefund()` |

## Konsep yang Diterapkan

- **State Machine** — transisi status dikontrol ketat, urutan tidak bisa dilangkahi
- **Immutability** — `amount` tidak bisa diubah setelah order tersimpan
- **Value Object** — `Money` mencegah amount negatif dan floating point error
- **Enum** — menggantikan Primitive Obsession pada field `status`
- **Audit Trail** — `AuditLog` bersifat immutable

## File

| File | Keterangan |
|------|------------|
| `OrderController_before.php` | Kode bermasalah |
| `Order_before.php` | Model sebelum perbaikan |
| `OrderStatus.php` | Enum status order |
| `Money.php` | Value Object untuk amount |
| `Order_after.php` | Deep Model + State Machine |
| `OrderController_after.php` | Controller tipis setelah perbaikan |
| `AuditLog.php` | Model immutable audit log |
| `Events/` | Event classes |
