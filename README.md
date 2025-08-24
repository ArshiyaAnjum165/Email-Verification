# 📧 PHP Email Verification & XKCD Comic Subscription System

This project is a **PHP-based email verification and subscription system** where users can:

- Register using their email and verify it via a **6-digit verification code**.
- Receive a **random XKCD comic every 24 hours** via email.
- **Unsubscribe** anytime through an email verification mechanism.
- Use a **CRON job** that automatically fetches and sends comics daily.

## 🚀 Features

### 1️⃣ Email Verification
- Users enter their email in the form.
- A **6-digit numeric code** is generated and sent via email.
- Users verify the code, and their email is saved in `registered_emails.txt`.

### 2️⃣ Unsubscribe Mechanism
- Every comic email contains an **unsubscribe link**.
- Users can unsubscribe by entering their email and verifying with a code.
- Once confirmed, their email is removed from `registered_emails.txt`.

### 3️⃣ XKCD Comic Subscription
- A **CRON job** runs every 24 hours.
- It fetches a **random XKCD comic** from the [XKCD API](https://xkcd.com/json.html).
- The comic is formatted in **HTML email** and sent to all registered users.
