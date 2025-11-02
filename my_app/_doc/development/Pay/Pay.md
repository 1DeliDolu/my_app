
# 💳 **Payment Flow – Modern Overview**

## 🎯 **Amaç**

Kullanıcı “Proceed to Checkout” → “Pay Now” aşamasında gerçek bir ödeme API’si olmadan **örnek (fake) bir ödeme süreci** yaşar.

Bu, Stripe, iyzico gibi gateway’lere geçmeden önce sistemin uçtan uca test edilebilmesini sağlar.

---

## ⚙️ **Teknik Akış**

### 1️⃣ **Kullanıcı akışı**

* **Cart** sayfasında → “Proceed to Checkout”
* **Checkout** sayfasında → adres seçip “Confirm Order”
* Ardından `/checkout/payment/{id}` sayfasına yönlendirilir
* Burada kart bilgilerini girip “Pay Now” butonuna basar

---

### 2️⃣ **Controller mantığı (`CheckoutController.php`)**

#### 🔹 Route: `/checkout/pay/{id}` → `app_checkout_pay`

```php
#[Route('/pay/{id}', name: 'app_checkout_pay', methods: ['POST'])]
public function pay(Order $order, EntityManagerInterface $em, MailService $mailer): Response
{
    $this->denyAccessUnlessGranted('ROLE_USER');
    if ($order->getUser() !== $this->getUser()) {
        throw $this->createAccessDeniedException();
    }

    // 💳 Fake ödeme simülasyonu
    sleep(2); // “işleniyor” efekti

    // 🟢 Sipariş güncellemesi
    $order->setStatus('Paid');
    $order->setUpdatedAt(new \DateTimeImmutable());
    $em->flush();

    // ✉️ Kullanıcıya onay e-postası gönder
    $mailer->sendOrderConfirmation($order);

    // 🔁 Kullanıcıya bildirim & yönlendirme
    $this->addFlash('success', 'Payment completed successfully! Confirmation email sent.');
    return $this->redirectToRoute('app_checkout_success', ['id' => $order->getId()]);
}
```

---

### 3️⃣ **Ne yapıyor bu adım?**

| Aşama                                | Açıklama                                                                                |
| ------------------------------------- | ----------------------------------------------------------------------------------------- |
| 🔐**Doğrulama**                | Giriş yapmış kullanıcı gerçekten bu siparişin sahibi mi kontrol eder.              |
| ⏳**sleep(2)**                  | Ödeme işlemi simülasyonu (gerçek gateway yerine).                                     |
| 💾**Durum güncelleme**         | Sipariş `Paid`olarak kaydedilir, tarih alanları yenilenir.                            |
| ✉️**MailService çağrısı** | `sendOrderConfirmation()`ile kullanıcının mail adresine sipariş özeti gönderilir. |
| ✅**Yönlendirme**              | Flash mesaj ve “Payment Success” sayfasına dönüş.                                   |

---

### 4️⃣ **MailService (src/Service/MailService.php)**

Basit bir HTML e-posta gönderici.

Mailtrap SMTP kullanır.

Gönderilen mailde ürün listesi, fiyatlar ve toplam tutar yer alır.

---

### 5️⃣ **Template: `/templates/checkout/success.html.twig`**

Kullanıcıya sade bir onay mesajı verir:

> “Your payment for order #123 was successful! Thank you for your purchase 🎉”

---

## 🧩 **Özet Görsel Akış**

```
[Cart] → [Checkout] → [Payment Form]
           ↓
      Fake Payment (sleep 2s)
           ↓
  Update Order → Send Email
           ↓
   [Success Page + Flash Message]
```

---

## 🚀 **Modern Durum (Current State)**

| Özellik                | Durum                                        |
| ----------------------- | -------------------------------------------- |
| 🧮 Sipariş kaydı      | ✔️ (Order + OrderItems + Total)            |
| 💳 Ödeme simülasyonu  | ✔️ (Fake, test amaçlı)                   |
| 📧 E-posta bildirimi    | ✔️ (Mailtrap entegrasyonu)                 |
| 🧭 Kullanıcı akışı | ✔️ Uçtan uca tamamlandı                  |
| 🔐 Güvenlik            | ✔️ Sadece sipariş sahibi ödeme yapabilir |

---

## 🧠 **Geleceğe Hazır Geliştirmeler**

| Adım                           | Açıklama                                   |
| ------------------------------- | -------------------------------------------- |
| 💰 Stripe / iyzico entegrasyonu | Gerçek ödeme API çağrıları eklenebilir |
| 🧾 Fatura PDF üretimi          | Ödeme sonrası otomatik PDF eklentisi       |
| 📧 Admin bildirim e-postası    | “Yeni sipariş alındı” mesajı           |
| 🕓 Order status updates         | Shipped / Delivered mail zinciri             |

---

✅ **Kısaca:**

Şu anda sistem tam bir “mock payment gateway” gibi çalışıyor:

* Güvenli (user-based kontrol)
* Sipariş güncellemesi yapıyor
* Mailtrap üzerinden onay e-postası gönderiyor
* Gerçek gateway entegrasyonuna hazır altyapı oluşturuyor

---
