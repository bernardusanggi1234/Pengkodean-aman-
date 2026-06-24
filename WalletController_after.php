# Security by Design — Laravel

Dokumentasi hasil praktikum Lab Security by Design menggunakan Laravel.
Setiap modul menunjukkan perbandingan kode bermasalah vs kode yang diperbaiki
dengan menerapkan prinsip-prinsip **Secure Coding** dan **Domain-Driven Design**.

---

## Modul

| Modul | Topik | Konsep Utama |
|-------|-------|--------------|
| [Modul 1](./Modul1_Authentication/) | Authentication & Login Security | Deep Model, Audit Trail, Session Ownership |
| [Modul 2](./Modul2_OrderRefund/) | Order & Refund System | State Machine, Immutability, Value Object |
| [Modul 3](./Modul3_EWallet/) | E-Wallet System | Pessimistic Locking, Atomic Transaction, Event-Driven |
| [Modul 4](./Modul4_Voucher/) | Voucher & Promo System | Idempotency, Race Condition Prevention, Enumeration Attack |

---

## Konsep Security by Design yang Diterapkan

| Konsep | Penjelasan |
|--------|-----------|
| **Deep Model** | Business logic keamanan dipindah masuk ke dalam model, bukan di controller |
| **Audit Trail** | Setiap aksi kritis dicatat (LoginAttempt, AuditLog, WalletTransaction, VoucherRedemption) |
| **State Machine** | Transisi status dikontrol ketat — urutan tidak bisa dilangkahi |
| **Immutability** | Field kritis (amount, usage_count) tidak bisa diubah setelah dibuat |
| **Value Object** | Money, VoucherCode, Discount — menghilangkan Primitive Obsession |
| **Pessimistic Locking** | `lockForUpdate()` mencegah race condition di level database |
| **Idempotency** | Request yang sama dikirim berkali-kali menghasilkan efek yang sama (tidak double) |

---

## Tech Stack

- **Framework:** Laravel (PHP)
- **Database:** MySQL / PostgreSQL
- **Pattern:** Domain-Driven Design (DDD), Event-Driven Architecture
