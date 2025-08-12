# Go-Live Checklist (Simple)

## 1. create_order.php
- Change `$keyId` and `$keySecret` to your **live** Razorpay credentials.

## 2. script.js
- Change `key` in `razorpayOptions` to your **live** Razorpay key.

## 3. index.html
- Update contact email and phone in the footer to your real details.

## 4. donations.csv
- Delete all test data before going live.
- Make sure this file is not publicly accessible (already protected by .htaccess).

## 5. General
- Add a privacy policy and terms page if required.
- Test the full donation flow with real payments on your live server.

---

**Do these changes before making your site live.** 