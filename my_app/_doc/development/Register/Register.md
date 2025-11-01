# 🧭 **Symfony Projesinde Register (Kayıt Ol) Özelliği – Tüm Aşamalar**

Bu özellik,  **müşterilerin kendi hesaplarını oluşturabilmesi** ,

kayıt sonrası **otomatik giriş yapabilmesi (auto-login)**

ve **MailTrap** üzerinden **“Welcome” e-postası alabilmesi** için geliştirildi.

Aşamaları 6 başlıkta anlatıyorum 👇

---

## 🧱 1️⃣ Registration Form’un Oluşturulması

İlk adımda Symfony’nin hazır komutuyla bir kayıt formu (controller, form class, template) oluşturuldu:

```bash
php bin/console make:registration-form
```

Bu komut otomatik olarak şunları yarattı:

* `src/Controller/RegistrationController.php`
* `src/Form/RegistrationFormType.php`
* `templates/registration/register.html.twig`

💡 **Amaç:** kullanıcıdan ad, soyad, e-posta ve parola bilgilerini almak.

---

## 🧩 2️⃣ RegistrationFormType (Form Alanları)

Kullanıcıdan alınacak alanlar belirlendi:

```php
$builder
    ->add('firstName', TextType::class)
    ->add('lastName', TextType::class)
    ->add('email', EmailType::class)
    ->add('plainPassword', RepeatedType::class, [
        'type' => PasswordType::class,
        'mapped' => false,
        ...
    ]);
```

💡 **Açıklama:**

* `plainPassword` veritabanına kaydedilmez (`mapped: false`) çünkü hash'lenmeden kaydedilmemeli.
* Parola iki kez giriliyor (tekrar kontrolü için).
* Validasyonlar eklendi: boş olmamalı, en az 6 karakter olmalı.

---

## 🧠 3️⃣ RegistrationController Mantığı

Controller tarafında kayıt işlemi şu adımlarla yapıldı 👇

### 📍 Aşamalar:

1. Form oluşturuldu:

   `$form = $this->createForm(RegistrationFormType::class, $user)`
2. Form submit edildiğinde validasyon kontrolü yapıldı.
3. Parola hash’lendi:

   ```php
   $hashedPassword = $passwordHasher->hashPassword($user, $form->get('plainPassword')->getData());
   $user->setPassword($hashedPassword);
   ```
4. Varsayılan roller atandı:

   ```php
   $user->setRoles(['ROLE_CUSTOMER']);
   $user->setType('customer');
   ```
5. Kullanıcı kaydedildi:

   ```php
   $entityManager->persist($user);
   $entityManager->flush();
   ```

💡 **Açıklama:**

* Yeni kayıt olan kullanıcı **otomatik olarak müşteri (customer)** rolüyle oluşturuluyor.
* Admin veya employee türleri sadece yönetici tarafından atanabiliyor.

---

## 🚀 4️⃣ Kayıt Sonrası Otomatik Login (Auto-Login)

Symfony’nin `UserAuthenticatorInterface` servisi kullanılarak, kullanıcı kayıt olduktan sonra **otomatik oturum açıyor.**

```php
return $userAuthenticator->authenticateUser(
    $user,
    $authenticator,
    $request
);
```

💡 **Açıklama:**

* `App\Security\AppAuthenticator` sınıfı üzerinden login işlemi tetikleniyor.
* Kullanıcı “Register” butonuna bastıktan hemen sonra login sayfasına gitmeden ana sayfaya yönlendiriliyor.
* `AppAuthenticator` içindeki `onAuthenticationSuccess()` metodu yönlendirmeyi belirliyor:
  ```php
  return new RedirectResponse($this->urlGenerator->generate('app_home'));
  ```

---

## 📨 5️⃣ MailTrap ile “Welcome Email”

Kayıt tamamlandığında kullanıcının e-postasına bir hoş geldin mesajı gönderiliyor.

### ✉️ MailService Sınıfı:

`src/Service/MailService.php`

```php
public function sendWelcomeEmail(string $to, string $name): void
{
    $email = (new Email())
        ->from('no-reply@myshop.com')
        ->to($to)
        ->subject('Welcome to MyShop!')
        ->html("<h2>Hello {$name},</h2><p>Welcome to MyShop!</p>");
    $this->mailer->send($email);
}
```

### 📡 `.env` içinde MailTrap ayarı:

```
MAILER_DSN=smtp://<username>:<password>@sandbox.smtp.mailtrap.io:2525
```

### 📬 Controller içinde:

```php
$mailService->sendWelcomeEmail($user->getEmail(), $user->getFirstName());
```

💡 **Açıklama:**

* MailTrap sanal SMTP ortamı olarak çalışıyor (test mailleri gerçek e-posta kutusuna gitmiyor).
* Böylece kayıt sonrası “Welcome!” mesajı geliştirici panelinde güvenli şekilde test ediliyor.

---

## 🧾 6️⃣ Template (Frontend Tarafı)

`templates/registration/register.html.twig` dosyasında Bootstrap ile modern bir form oluşturuldu:

```twig
<h2>Create an Account</h2>
{{ form_start(registrationForm) }}
    {{ form_row(registrationForm.firstName) }}
    {{ form_row(registrationForm.lastName) }}
    {{ form_row(registrationForm.email) }}
    {{ form_row(registrationForm.plainPassword.first) }}
    {{ form_row(registrationForm.plainPassword.second) }}
    <button class="btn btn-primary w-100 mt-3">Register</button>
{{ form_end(registrationForm) }}
```

💡 **Açıklama:**

* Her alan Bootstrap sınıflarıyla stillendi.
* Form `registrationForm` üzerinden otomatik CSRF koruması içeriyor.
* Başarılı kayıt sonrası ana sayfaya yönlendiriliyor.

---

## ✅ **Sonuç (Özet Tablo)**

| Adım | İşlem                                                  | Sonuç                                               |
| ----- | -------------------------------------------------------- | ---------------------------------------------------- |
| 1     | `make:registration-form`ile form yapısı oluşturuldu | Symfony’ye entegre kayıt sistemi hazırlandı      |
| 2     | `RegistrationFormType`düzenlendi                      | Kullanıcıdan ad, soyad, e-posta, parola alınıyor |
| 3     | Parola hashing ve role ataması eklendi                  | Yeni kullanıcı güvenli şekilde kayıt oluyor     |
| 4     | `authenticateUser()`kullanıldı                       | Kayıt sonrası otomatik login ✅                    |
| 5     | `MailService`eklendi, MailTrap bağlantısı yapıldı | Welcome e-postası gönderiliyor                     |
| 6     | Form şablonu Bootstrap ile tasarlandı                  | Kullanıcı dostu arayüz ✅                         |

---

## 💡 Şu Anda Sistem Ne Durumda?

✅ `/register` → aktif

✅ Kullanıcı kayıt olabilir

✅ Şifre hash’lenir

✅ Otomatik giriş yapılır

✅ MailTrap’e hoş geldin e-postası gider

✅ Navbar’da “Welcome, [name]” görünür

---
