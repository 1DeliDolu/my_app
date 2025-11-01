### 🛡️ CSRF Koruması Nasıl Uygulanır

CSRF veya  **Cross-site request forgery** , kötü niyetli bir kişinin, bir kullanıcının bilgisi veya rızası olmadan bir web uygulamasında işlem yapmasını sağlayan bir saldırı türüdür.

Bu saldırı, bir web uygulamasının bir kullanıcının tarayıcısına duyduğu güvene (örneğin oturum çerezlerine) dayanır. İşte gerçek bir CSRF saldırısına örnek:

```html
<html>
    <body>
        <form action="https://example.com/settings/update-email" method="POST">
            <input type="hidden" name="email" value="malicious-actor-address@some-domain.com"/>
        </form>
        <script>
            document.forms[0].submit();
        </script>

        <!-- kullanıcıyı oyalamak için bazı içerikler -->
    </body>
</html>
```

Bu siteyi (örneğin bir e-posta bağlantısına tıklayarak veya sosyal medya gönderisine tıklayarak) ziyaret ederseniz ve hâlihazırda `https://example.com` sitesinde oturumunuz açık ise, kötü niyetli kişi siz farkında bile olmadan hesabınıza bağlı e-posta adresini değiştirebilir (böylece hesabınızı ele geçirebilir).

CSRF saldırılarını önlemenin etkili bir yolu **anti-CSRF tokenleri** kullanmaktır. Bunlar, formlara gizli alanlar olarak eklenen benzersiz tokenlerdir. Meşru sunucu bu tokenleri doğrulayarak isteğin beklenen kaynaktan gelip gelmediğini kontrol eder.

Anti-CSRF tokenleri iki şekilde yönetilebilir:

* **Stateful yaklaşım:** Tokenler oturumda saklanır ve kullanıcı/eylem bazında benzersizdir.
* **Stateless yaklaşım:** Tokenler istemci tarafında üretilir.

---

### ⚙️ Kurulum

Symfony, anti-CSRF tokenleri oluşturmak ve doğrulamak için gerekli tüm özellikleri sağlar. Kullanım öncesinde şu paketi projeye yükleyin:

```bash
composer require symfony/security-csrf
```

Ardından `csrf_protection` seçeneğiyle CSRF korumasını etkinleştirin/devre dışı bırakın (daha fazla bilgi için CSRF yapılandırma referansına bakın):

```php
// config/packages/framework.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework->csrfProtection()
        ->enabled(true)
    ;
};
```

Varsayılan olarak CSRF korumasında kullanılan tokenler oturumda saklanır. Bu nedenle, CSRF korumalı bir form oluşturulduğunda bir oturum otomatik olarak başlatılır.

Bu durum, CSRF korumalı formlar içeren sayfaların önbelleğe alınması için çeşitli stratejilere yol açar:

* Formu, önbelleğe alınmayan bir ESI parçası olarak gömün ve sayfanın geri kalanını önbelleğe alın.
* Sayfanın tamamını önbelleğe alın ve formu önbelleğe alınmayan bir AJAX isteğiyle yükleyin.
* Sayfanın tamamını önbelleğe alın ve `hinclude.js` kullanarak CSRF tokenini AJAX ile yükleyip form alanında değiştirin.

CSRF korumalı formlar içeren sayfaları önbelleğe almanın en etkili yolu, aşağıda açıklanan **stateless CSRF tokenleri** kullanmaktır.

---

### 🧾 Symfony Formlarında CSRF Koruması

Symfony formları varsayılan olarak CSRF tokenleri içerir ve Symfony bunları otomatik olarak kontrol eder. Yani Symfony Formlarını kullanırken, CSRF saldırılarına karşı korunmak için ek bir işlem yapmanız gerekmez.

Varsayılan olarak Symfony, CSRF tokenini `_csrf_token` adlı gizli bir alana ekler, ancak bu alan adı hem **global** hem de **form bazında** özelleştirilebilir.

Global olarak, `framework.form` seçeneği altında yapılandırabilirsiniz:

```php
// config/packages/framework.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework) {
    $framework->form()->csrfProtection()
        ->enabled(true)
        ->fieldName('custom_token_name')
    ;
};
```

Form bazında, `setDefaults()` metodunda yapılandırabilirsiniz:

```php
// src/Form/TaskType.php
namespace App\Form;

// ...
use App\Entity\Task;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'      => Task::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'task_item',
        ]);
    }
}
```

Form alanının CSRF kısmını özelleştirmek için özel bir form teması oluşturabilir ve `csrf_token` önekini kullanabilirsiniz (örneğin `{% block csrf_token_widget %}` bloğu).

---

### 🔐 Giriş Formu ve Çıkış İşleminde CSRF Koruması

Şunları okuyun:

* **CSRF Protection in Login Forms**
* **CSRF protection for the logout action**

---

### 🧩 CSRF Tokenlerini Manuel Olarak Üretme ve Kontrol Etme

Symfony Formları otomatik CSRF koruması sağlasa da, bazen Symfony Form bileşenini kullanmayan basit HTML formları için manuel işlem gerekebilir.

Örneğin bir öğeyi silmeye yarayan bir form düşünelim. İlk olarak Twig şablonunda `csrf_token()` fonksiyonunu kullanarak bir token üretin:

```twig
<form action="{{ url('admin_post_delete', { id: post.id }) }}" method="post">
    <input type="hidden" name="token" value="{{ csrf_token('delete-item') }}">
    <button type="submit">Delete item</button>
</form>
```

Sonra denetleyicide bu tokeni alın ve `isCsrfTokenValid()` metoduyla doğrulayın:

```php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

public function delete(Request $request): Response
{
    $submittedToken = $request->getPayload()->get('token');

    if ($this->isCsrfTokenValid('delete-item', $submittedToken)) {
        // ... işlem yap (örneğin nesneyi sil)
    }
}
```

Alternatif olarak, `IsCsrfTokenValid` özniteliğini kullanabilirsiniz:

```php
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;

#[IsCsrfTokenValid('delete-item', tokenKey: 'token')]
public function delete(): Response
{
    // ... işlem yap
}
```

Her öğe için ayrı tokenler istiyorsanız:

```twig
<form action="{{ url('admin_post_delete', { id: post.id }) }}" method="post">
    <input type="hidden" name="token" value="{{ csrf_token('delete-item-' ~ post.id) }}">
    <button type="submit">Delete item</button>
</form>
```

Bu öznitelik bir **controller sınıfına** da uygulanabilir; böylece o sınıftaki tüm işlemler için CSRF doğrulaması yapılır:

```php
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;

#[IsCsrfTokenValid('the token ID')]
final class SomeController extends AbstractController
{
    // ...
}
```

`IsCsrfTokenValid` özniteliği ayrıca bir **Expression** nesnesiyle de kullanılabilir:

```php
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\ExpressionLanguage\Expression;

#[IsCsrfTokenValid(new Expression('"delete-item-" ~ args["post"].getId()'), tokenKey: 'token')]
public function delete(Post $post): Response
{
    // ... işlem yap
}
```

Varsayılan olarak `IsCsrfTokenValid` özniteliği tüm HTTP metodları için kontrol yapar.

Ancak yalnızca belirli metodlarda çalışmasını isterseniz `methods` parametresini kullanabilirsiniz:

```php
#[IsCsrfTokenValid('delete-item', tokenKey: 'token', methods: ['DELETE'])]
public function delete(Post $post): Response
{
    // ... nesneyi sil
}
```

🧩 **Symfony 7.1:** `IsCsrfTokenValid` özniteliği eklendi.

⚙️ **Symfony 7.3:** `methods` parametresi eklendi.


# 🔐 CSRF Tokenleri ve Sıkıştırma Yan Kanal Saldırıları

BREACH ve CRIME, HTTP sıkıştırması kullanıldığında HTTPS'e karşı gerçekleştirilen güvenlik açıklarıdır. Saldırganlar, sıkıştırmadan sızan bilgileri kullanarak şifrelenmiş metnin hedeflenmiş parçalarını elde edebilirler. Bu saldırıları hafifletmek ve bir saldırganın CSRF tokenlerini tahmin etmesini önlemek için, tokeni karıştırmak üzere rastgele bir maske tokenin önüne eklenir ve kullanılır.

## ⚙️ Durumsuz (Stateless) CSRF Tokenleri

7.2

Stateless anti-CSRF koruması Symfony 7.2 ile tanıtıldı.

Geleneksel olarak CSRF tokenleri stateful'dır; yani oturumda saklanırlar. Ancak bazı token ID'leri `stateless_token_ids` seçeneği kullanılarak durumsuz (stateless) olarak ilan edilebilir. Stateless CSRF tokenleri, Symfony Flex kullanan uygulamalarda varsayılan olarak etkinleştirilir.

```php
// config/packages/csrf.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework->csrfProtection()
        ->statelessTokenIds(['submit', 'authenticate', 'logout'])
    ;
};
```

Stateless CSRF tokenleri, oturuma güvenmeden koruma sağlar. Bu, CSRF koruması gerektiren sayfaları tam olarak önbelleğe almanıza olanak tanır.

Durumsuz bir CSRF tokeni doğrulanırken, Symfony gelen HTTP isteğinin `Origin` ve `Referer` başlıklarını kontrol eder. Bu başlıklardan herhangi biri uygulamanın hedef origin'i (yani domaini) ile eşleşiyorsa, token geçerli sayılır.

Bu mekanizma, uygulamanın kendi origin'ini belirleyebilmesine dayanır. Eğer bir reverse proxy arkasındaysanız, onun düzgün yapılandırıldığından emin olun. Bkz. *How to Configure Symfony to Work behind a Load Balancer or a Reverse Proxy.*

## 🆔 Varsayılan Token ID Kullanımı

Stateful CSRF tokenleri tipik olarak form veya işlem bazında kapsamlandırılırken, stateless tokenler çok fazla tanımlayıcıya ihtiyaç duymaz.

Yukarıdaki örnekte `authenticate` ve `logout` tanımlayıcıları, Symfony Security bileşeninde varsayılan olarak kullanıldıkları için listelenmiştir. `submit` tanımlayıcısı ise uygulama tarafından tanımlanan form türlerinin de varsayılan olarak CSRF korumasını kullanabilmesi için eklenmiştir.

Aşağıdaki yapılandırma yalnızca autoconfiguration ile kaydedilen form türlerine (ki bu kendi servisleriniz için varsayılandır) uygulanır ve `submit`'i onların varsayılan token tanımlayıcısı olarak ayarlar:

```php
// config/packages/csrf.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework->form()
        ->csrfProtection()
            ->tokenId('submit')
    ;
};
```

Yukarıda `stateless_token_ids` seçeneğinde listelenen bir token tanımlayıcısıyla yapılandırılan formlar, durumsuz CSRF korumasını kullanacaktır.

## 🧭 Javascript Kullanarak CSRF Tokeni Üretme

`Origin` ve `Referer` HTTP başlıklarına ek olarak, durumsuz CSRF koruması bir çerez ve bir başlık (varsayılan olarak `csrf-token` adlı) kullanılarak da tokenleri doğrulayabilir. (Bkz. CSRF yapılandırma referansı.)

Bu ek kontroller, durumsuz CSRF korumasının sunduğu savunma derinliğinin parçasıdır. Bunlar isteğe bağlıdır ve bazı JavaScript'in etkinleştirilmesini gerektirir. Bu JavaScript, bir form gönderildiğinde kriptografik olarak güvenli rastgele bir token üretir. Daha sonra bu tokeni formun gizli CSRF alanına ekler ve hem bir çerez hem de istek başlığında gönderir.

Sunucu tarafında CSRF token doğrulaması, çerez ve başlıktaki değerleri karşılaştırır. Bu "double-submit" koruması tarayıcının same-origin politikasına dayanır ve şu şekilde daha da sertleştirilir:

* Her gönderim için yeni bir token üretilir (çerez fixation'ı önlemek için);
* `samesite=strict` ve `__Host-` çerez attribute'ları kullanılır (HTTPS'i zorunlu kılmak ve çerezi mevcut domain ile sınırlamak için).

Varsayılan olarak, Symfony JavaScript snippet'i gizli CSRF alanının `_csrf_token` olarak adlandırılmasını veya `data-controller="csrf-protection"` attribute'unu içermesini bekler. Aynı protokol takip edildiği sürece bu mantığı ihtiyaçlarınıza göre uyarlayabilirsiniz.

Doğrulamanın düşürülmesini (downgrade) önlemek için ekstra bir davranış kontrolü yapılır: eğer (ve yalnızca eğer) zaten bir oturum mevcutsa, başarılı "double-submit" hatırlanır ve gelecekteki istekler için zorunlu hale gelir. Bu, isteğe bağlı çerez/başlık doğrulaması bir kez etkili olarak kanıtlandığında, bunun o oturum için sürdürülmesini sağlar.

Tüm isteklerde "double-submit" doğrulamasını zorunlu kılmak önerilmez; çünkü bu, kullanıcı deneyimini bozabilir. Yukarıda açıklanan fırsatçı (opportunistic) yaklaşım tercih edilir; JavaScript ulaşılamadığında uygulamanın `Origin` / `Referer` kontrollerine zarifçe geri dönmesine izin verir.
