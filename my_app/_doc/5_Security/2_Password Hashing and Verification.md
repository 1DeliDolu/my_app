
### 🔐 Parola Hashleme ve Doğrulama

Çoğu uygulama, kullanıcıların giriş yapması için parolalar kullanır. Bu parolalar güvenli bir şekilde saklanabilmeleri için hashlenmelidir. Symfony’nin **PasswordHasher** bileşeni, parolaları güvenli bir şekilde hashlemek ve doğrulamak için tüm yardımcı araçları sağlar.

Şu komutu çalıştırarak kurulu olduğundan emin olun:

```
composer require symfony/password-hasher
```

---

### ⚙️ Bir Parola Hashleyici Yapılandırma

Parolaları hashlemeden önce, `password_hashers` seçeneğini kullanarak bir hashleyici yapılandırmanız gerekir. Hashleme algoritmasını ve isteğe bağlı olarak bazı algoritma seçeneklerini yapılandırmalısınız:

```php
// config/packages/security.php
use App\Entity\User;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Config\SecurityConfig;

return static function (SecurityConfig $security): void {
    // ...

    // User sınıfı (ve alt sınıfları) için varsayılan seçeneklerle otomatik hashleyici
    $security->passwordHasher(User::class)
        ->algorithm('auto');

    // Tüm PasswordAuthenticatedUserInterface örnekleri için özel seçeneklerle otomatik hashleyici
    $security->passwordHasher(PasswordAuthenticatedUserInterface::class)
        ->algorithm('auto')
        ->cost(15);
};
```

Bu örnekte **“auto”** algoritması kullanılır. Bu hashleyici, sisteminizde mevcut olan en güvenli algoritmayı otomatik olarak seçer. Parola geçişiyle (password migration) birleştirildiğinde, gelecekte yeni algoritmalar eklense bile parolalarınızı her zaman en güvenli şekilde korumanızı sağlar.

Bu makalenin ilerleyen kısmında, desteklenen tüm algoritmaların tam referansını bulabilirsiniz.

---

### 🚀 Test Ortamında Hashleme Hızını Artırma

Parola hashleme kaynak açısından yoğun bir işlemdir ve güvenli parola hashleri üretmek için zaman alır. Bu, parolalarınızın güvenliğini artırır.

Ancak testlerde güvenli hashlerin önemi yoktur, bu yüzden test ortamında yapılandırmayı değiştirerek testleri daha hızlı çalıştırabilirsiniz:

```php
// config/packages/security.php
use App\Entity\User;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Config\SecurityConfig;

return static function (SecurityConfig $security, ContainerConfigurator $container): void {
    // ...

    if ('test' === $container->env()) {
        // Burada kullanıcı sınıfınızın adını kullanın
        $security->passwordHasher(User::class)
            ->algorithm('auto') // config/packages/security.yaml dosyasındaki değerle aynı olmalı
            ->cost(4) // bcrypt için mümkün olan en düşük değer
            ->timeCost(2) // argon için mümkün olan en düşük değer
            ->memoryCost(10) // argon için mümkün olan en düşük değer
        ;
    }
};
```

---

### 🧩 Parolayı Hashleme

Doğru algoritmayı yapılandırdıktan sonra,  **UserPasswordHasherInterface** ’i kullanarak parolaları hashleyebilir ve doğrulayabilirsiniz:

```php
// src/Controller/RegistrationController.php
namespace App\Controller;

// ...
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    public function registration(UserPasswordHasherInterface $passwordHasher): Response
    {
        // ... örneğin bir kayıt formundan kullanıcı verilerini alın
        $user = new User(...);
        $plaintextPassword = ...;

        // Parolayı hashle (security.yaml’deki $user sınıfına göre)
        $hashedPassword = $passwordHasher->hashPassword(
            $user,
            $plaintextPassword
        );
        $user->setPassword($hashedPassword);

        // ...
    }

    public function delete(UserPasswordHasherInterface $passwordHasher, UserInterface $user): void
    {
        // ... örneğin “silme onayı” penceresinden parolayı alın
        $plaintextPassword = ...;

        if (!$passwordHasher->isPasswordValid($user, $plaintextPassword)) {
            throw new AccessDeniedHttpException();
        }
    }
}
```

---

### 🔁 Parola Sıfırlama

**MakerBundle** ve **SymfonyCastsResetPasswordBundle** kullanarak unutulan parolaları yönetmek için kutudan çıktığı gibi güvenli bir çözüm oluşturabilirsiniz.

Önce bundle’ı kurun:

```
composer require symfonycasts/reset-password-bundle
```

Ardından şu komutu kullanın:

```
php bin/console make:reset-password
```

Bu komut uygulamanızla ilgili birkaç soru sorar ve ihtiyacınız olan tüm dosyaları oluşturur. Ardından, başarılı bir mesaj ve yapmanız gereken diğer adımların listesini görürsünüz.

MakerBundle sürüm **v1.57.0** itibarıyla `--with-uuid` veya `--with-ulid` seçeneklerini geçebilirsiniz. Symfony’nin  **Uid Component** ’ini kullanarak, varlıklarınızın `id` tipi `int` yerine `Uuid` veya `Ulid` olarak oluşturulabilir.

`reset_password.yaml` dosyasını güncelleyerek bu bundle’ın davranışını özelleştirebilirsiniz. Daha fazla bilgi için **SymfonyCastsResetPasswordBundle** kılavuzuna bakın.

---

### 🔄 Parola Geçişi (Password Migration)

Parolaları korumak için, bunları en güncel hash algoritmalarıyla saklamak önerilir. Yani sisteminizde daha iyi bir hash algoritması mevcutsa, kullanıcının parolası bu yeni algoritmayla yeniden hashlenmeli ve saklanmalıdır. Bu, `migrate_from` seçeneğiyle mümkündür.

#### Yeni bir Hashleyici “migrate_from” Kullanarak Yapılandırma

```php
// config/packages/security.php
use Symfony\Config\SecurityConfig;

return static function (SecurityConfig $security): void {
    // ...
    $security->passwordHasher('legacy')
        ->algorithm('sha256')
        ->encodeAsBase64(true)
        ->iterations(1)
    ;

    $security->passwordHasher('App\Entity\User')
        // yeni hashleyici ve seçenekleri
        ->algorithm('sodium')
        ->migrateFrom([
            'bcrypt', // varsayılan seçeneklerle “bcrypt” hashleyicisini kullanır
            'legacy', // yukarıda yapılandırılmış “legacy” hashleyicisini kullanır
        ])
    ;
};
```

Bu yapılandırmayla:

* Yeni kullanıcılar yeni algoritmayla hashlenir;
* Parolası eski algoritmayla saklanan bir kullanıcı giriş yaptığında, Symfony önce eski algoritmayla doğrular, ardından yeni algoritmayla yeniden hashleyip günceller.

 **auto** ,  **native** , **bcrypt** ve **argon** hashleyicileri, şu `migrate_from` algoritmalarını otomatik olarak etkinleştirir:

* PBKDF2 (hash_pbkdf2 kullanır)
* Message digest (hash kullanır)

Her ikisi de `hash_algorithm` ayarını algoritma olarak kullanır. `auto` hashleyici kullanılmadıkça `hash_algorithm` yerine `migrate_from` kullanılması önerilir.

---

### 🧱 Parolayı Yükseltme (Upgrade the Password)

Başarılı bir girişten sonra, Security sistemi daha iyi bir algoritma mevcutsa kullanıcı parolasını bu yeni algoritmayla yeniden hashler. Özel bir authenticator kullanıyorsanız, `PasswordCredentials`’ı security passport içinde kullanmalısınız.

Bu davranışı etkinleştirmek için yeni hashlenmiş parolanın nasıl saklanacağını belirtmeniz gerekir:

* Doctrine’in entity user provider’ını kullanırken
* Özel bir user provider kullanırken

Bundan sonra işlem tamamdır: parolalarınız her zaman mümkün olan en güvenli şekilde hashlenir!

Symfony uygulaması dışında **PasswordHasher** bileşenini kullanıyorsanız, manuel olarak `PasswordHasherInterface::needsRehash()` ile yeniden hash gerekip gerekmediğini kontrol etmeli ve `PasswordHasherInterface::hash()` ile düz metin parolayı yeniden hashlemelisiniz.

---

### 🗃️ Doctrine Kullanırken Parolayı Yükseltme

Entity user provider kullanırken, `UserRepository` içinde  **PasswordUpgraderInterface** ’i uygulayın. Bu arayüz yeni oluşturulan parola hash’ini saklama işlemini gerçekleştirir:

```php
// src/Repository/UserRepository.php
namespace App\Repository;

// ...
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

class UserRepository extends EntityRepository implements PasswordUpgraderInterface
{
    // ...

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        // Yeni hashlenmiş parolayı User nesnesine ayarla
        $user->setPassword($newHashedPassword);

        // Veritabanındaki sorguları çalıştır
        $this->getEntityManager()->flush();
    }
}
```

---

### 🧩 Özel Bir User Provider Kullanırken Parolayı Yükseltme

Özel bir user provider kullanıyorsanız,  **PasswordUpgraderInterface** ’i user provider içinde uygulayın:

```php
// src/Security/UserProvider.php
namespace App\Security;

// ...
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

class UserProvider implements UserProviderInterface, PasswordUpgraderInterface
{
    // ...

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        // Yeni hashlenmiş parolayı User nesnesine ayarla
        $user->setPassword($newHashedPassword);

        // ... yeni parolayı sakla
    }
}
```


### 🔄 Özel Bir Hashleyiciden Parola Geçişini Tetikleme

Özel bir parola hashleyici kullanıyorsanız, `needsRehash()` metodunda `true` döndürerek parola geçişini tetikleyebilirsiniz:

```php
// src/Security/CustomPasswordHasher.php
namespace App\Security;

// ...
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

class CustomPasswordHasher implements PasswordHasherInterface
{
    // ...

    public function needsRehash(string $hashedPassword): bool
    {
        // mevcut parolanın eski bir hashleyiciyle hashlenip hashlenmediğini kontrol et
        $hashIsOutdated = ...;

        return $hashIsOutdated;
    }
}
```

---

### ⚙️ Dinamik Parola Hashleyiciler

Genellikle aynı parola hashleyici, belirli bir sınıfın tüm örneklerine uygulanacak şekilde yapılandırılır. Ancak, bir “isimlendirilmiş” hashleyici kullanarak hangi hashleyicinin dinamik olarak kullanılacağını seçmek de mümkündür.

Varsayılan olarak (makalenin başında gösterildiği gibi), **App\Entity\User** için `auto` algoritması kullanılır.

Bu sıradan bir kullanıcı için yeterince güvenli olabilir, ancak örneğin yöneticilerinizin daha güçlü bir algoritma (örneğin daha yüksek cost değeriyle `auto`) kullanmasını isterseniz, bunu isimlendirilmiş hashleyicilerle yapabilirsiniz:

```php
// config/packages/security.php
use Symfony\Config\SecurityConfig;

return static function (SecurityConfig $security): void {
    // ...
    $security->passwordHasher('harsh')
        ->algorithm('auto')
        ->cost(15)
    ;
};
```

Bu, **harsh** adında bir hashleyici oluşturur. Bir **User** örneğinin bunu kullanabilmesi için, sınıfın **PasswordHasherAwareInterface** arayüzünü uygulaması gerekir. Bu arayüz bir metod gerektirir — `getPasswordHasherName()` — ve bu metod kullanılacak hashleyicinin adını döndürmelidir:

```php
// src/Entity/User.php
namespace App\Entity;

use Symfony\Component\PasswordHasher\Hasher\PasswordHasherAwareInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class User implements
    UserInterface,
    PasswordAuthenticatedUserInterface,
    PasswordHasherAwareInterface
{
    // ...

    public function getPasswordHasherName(): ?string
    {
        if ($this->isAdmin()) {
            return 'harsh';
        }

        return null; // varsayılan hashleyiciyi kullan
    }
}
```

Parolaları taşırken (`migrating passwords`), eski hashleyici adını döndürmek için  **PasswordHasherAwareInterface** ’i uygulamanız gerekmez: Symfony bunu `migrate_from` yapılandırmanızdan otomatik olarak algılar.

Eğer  **PasswordHasherInterface** ’i uygulayan kendi parola hashleyicinizi oluşturduysanız, bunu isimlendirilmiş hashleyici olarak kullanabilmek için bir servis olarak kaydetmeniz gerekir:

```php
// config/packages/security.php
use App\Security\Hasher\MyCustomPasswordHasher;
use Symfony\Config\SecurityConfig;

return static function (SecurityConfig $security): void {
    // ...
    $security->passwordHasher('app_hasher')
        ->id(MyCustomPasswordHasher::class)
    ;
};
```

Bu, **App\Security\Hasher\MyCustomPasswordHasher** servis kimliğinden oluşturulmuş **app_hasher** adlı bir hashleyici oluşturur.

---

### 🔣 Bağımsız Bir String’i Hashleme

Parola hashleyici, kullanıcıdan bağımsız olarak string’leri hashlemek için de kullanılabilir. **PasswordHasherFactory** kullanarak birden fazla hashleyici tanımlayabilir, bunlardan birini adıyla alabilir ve hash oluşturabilirsiniz. Daha sonra bir string’in belirtilen hash ile eşleşip eşleşmediğini doğrulayabilirsiniz:

```php
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;

// farklı hashleyicileri fabrika üzerinden yapılandır
$factory = new PasswordHasherFactory([
    'common' => ['algorithm' => 'bcrypt'],
    'sodium' => ['algorithm' => 'sodium'],
]);

// bcrypt kullanan hashleyiciyi al
$hasher = $factory->getPasswordHasher('common');
$hash = $hasher->hash('plain');

// verilen string’in yukarıda hesaplanan hash ile eşleşip eşleşmediğini doğrula
$hasher->verify($hash, 'invalid'); // false
$hasher->verify($hash, 'plain'); // true
```

---

### 🔐 Desteklenen Algoritmalar

* `auto`
* `bcrypt`
* `sodium`
* `PBKDF2`
* veya kendi özel hashleyicinizi oluşturabilirsiniz

---

### ⚡ “auto” Hashleyici

Sisteminizde mevcut olan en iyi hashleyiciyi otomatik olarak seçer (şu anda  **Bcrypt** ). Gelecekte PHP veya Symfony yeni hashleyiciler eklerse, farklı bir hashleyici seçilebilir.

Bu nedenle, hashlenmiş parolaların uzunluğu gelecekte değişebilir; bu yüzden veritabanında bunların saklanabilmesi için yeterli alan ayırdığınızdan emin olun (**varchar(255)** iyi bir ayardır).

---

### 🔒 Bcrypt Parola Hashleyici

**bcrypt** parola hashleme fonksiyonunu kullanarak 60 karakter uzunluğunda hashlenmiş parolalar üretir. Bu parolalar kendi içlerinde otomatik olarak üretilen kriptografik salt içerir, dolayısıyla salt işlemini sizin yapmanıza gerek yoktur.

Yalnızca bir yapılandırma seçeneği vardır:  **cost** .

Bu, 4–31 aralığında bir tam sayıdır (varsayılan olarak 13). Her bir artış, parolanın hashlenme süresini iki katına çıkarır. Bu şekilde tasarlanmıştır, böylece parola güvenliği işlem gücündeki gelişmelere uyum sağlayabilir.

İstediğiniz zaman cost değerini değiştirebilirsiniz — daha önce farklı bir cost değeriyle hashlenmiş parolalar yine doğrulanabilir. Yeni parolalar yeni cost değeriyle hashlenir.

💡 Test ortamında BCrypt kullanırken testleri çok daha hızlı hale getirmek için cost değerini 4 (izin verilen minimum) olarak ayarlayabilirsiniz.

---

### 🧱 Sodium Parola Hashleyici

**Argon2** anahtar türetme fonksiyonunu kullanır. Argon2 desteği PHP 7.2 ile **libsodium** uzantısının eklenmesiyle tanıtılmıştır.

Hashlenmiş parolalar **96 karakter** uzunluğundadır, ancak hash sonucu içine gömülü parametreler nedeniyle bu uzunluk gelecekte değişebilir. Bu yüzden veritabanında yeterli alan ayırdığınızdan emin olun. Ayrıca, her yeni parola için otomatik olarak kriptografik salt üretilir.

---

### ⚠️ PBKDF2 Hashleyici

PHP, **Sodium** ve **Bcrypt** desteği eklediğinden beri **PBKDF2** hashleyicisini kullanmak artık önerilmez.

Bu hashleyiciyi kullanan eski uygulamaların yeni algoritmalara geçmesi tavsiye edilir.

---

### 🧩 Özel Bir Parola Hashleyici Oluşturma

Kendi hashleyicinizi oluşturmanız gerekiyorsa, şu kurallara uymanız gerekir:

* Sınıf  **PasswordHasherInterface** ’i uygulamalıdır

  (hash algoritmanız ayrı bir salt kullanıyorsa **LegacyPasswordHasherInterface** de uygulanabilir)
* `hash()` ve `verify()` metotlarının, parolanın **4096 karakterden uzun olmadığını** doğrulaması gerekir.

  Bu, güvenlik nedeniyledir (bkz.  **CVE-2013-5750** ).

Bu kontrolü yapmak için `isPasswordTooLong()` metodunu kullanabilirsiniz:

```php
// src/Security/Hasher/CustomVerySecureHasher.php
namespace App\Security\Hasher;

use Symfony\Component\PasswordHasher\Exception\InvalidPasswordException;
use Symfony\Component\PasswordHasher\Hasher\CheckPasswordLengthTrait;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

class CustomVerySecureHasher implements PasswordHasherInterface
{
    use CheckPasswordLengthTrait;

    public function hash(string $plainPassword): string
    {
        if ($this->isPasswordTooLong($plainPassword)) {
            throw new InvalidPasswordException();
        }

        // ... düz metin parolayı güvenli bir şekilde hashle

        return $hashedPassword;
    }

    public function verify(string $hashedPassword, string $plainPassword): bool
    {
        if ('' === $plainPassword || $this->isPasswordTooLong($plainPassword)) {
            return false;
        }

        // ... parolanın kullanıcı parolasıyla güvenli bir şekilde eşleşip eşleşmediğini doğrula

        return $passwordIsValid;
    }

    public function needsRehash(string $hashedPassword): bool
    {
        // Bir parola hashinin yeniden hashlenmesinin faydalı olup olmayacağını kontrol et
        return $needsRehash;
    }
}
```

Şimdi, **id** ayarını kullanarak bir parola hashleyici tanımlayın:

```php
// config/packages/security.php
use App\Security\Hasher\CustomVerySecureHasher;
use Symfony\Config\SecurityConfig;

return static function (SecurityConfig $security): void {
    // ...
    $security->passwordHasher('app_hasher')
        // özel hashleyicinizin servis kimliği (varsayılan services.yaml kullanılarak FQCN)
        ->id(CustomVerySecureHasher::class)
    ;
};
```
