## 🧩 Doğrulama (Validation)

Web uygulamalarında doğrulama çok yaygın bir görevdir. Formlara girilen verilerin doğrulanması gerekir. Veriler ayrıca bir veritabanına yazılmadan veya bir web servisine gönderilmeden önce de doğrulanmalıdır.

Symfony, bunu sizin için halletmek üzere bir **Validator** bileşeni sağlar. Bu bileşen, **JSR303 Bean Validation** spesifikasyonuna dayanır.

---

### ⚙️ Kurulum

Symfony Flex kullanan uygulamalarda, doğrulayıcıyı kullanmadan önce şu komutu çalıştırın:

```
composer require symfony/validator
```

Uygulamanız Symfony Flex kullanmıyorsa, doğrulamayı etkinleştirmek için bazı manuel yapılandırmalar yapmanız gerekebilir. **Validation configuration reference** sayfasına bakın.

---

### 🧠 Doğrulamanın Temelleri

Doğrulamayı anlamanın en iyi yolu, onu uygulamada görmektir. Başlamak için, uygulamanızda bir yerde kullanmanız gereken basit bir PHP nesnesi oluşturduğunuzu varsayalım:

```php
// src/Entity/Author.php
namespace App\Entity;

class Author
{
    private string $name;
}
```

Bu noktaya kadar, bu sınıf uygulamanız içinde belirli bir amaç için hizmet eden sıradan bir sınıftır.

Doğrulamanın amacı, bir nesnenin verilerinin geçerli olup olmadığını size bildirmektir.

Bunun için, nesnenin geçerli sayılması için uyması gereken bir dizi kural (constraint) tanımlarsınız.

Bu kurallar genellikle PHP koduyla veya  **attribute** ’larla tanımlanır, ancak ayrıca `config/validator/` dizini altındaki `.yaml` veya `.xml` dosyalarında da tanımlanabilir.

Örneğin, `$name` özelliğinin boş olmaması gerektiğini belirtmek için aşağıdakini ekleyin:

```php
// src/Entity/Author.php
namespace App\Entity;
// ...
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class Author
{
    private string $name;

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addPropertyConstraint('name', new NotBlank());
    }
}
```

Bu yapılandırmayı eklemek tek başına değerin boş olmamasını garanti etmez; yine de boş bir değer atayabilirsiniz.

Değerin gerçekten kurala uyduğundan emin olmak için nesnenin  **validator service** ’e iletilmesi gerekir.

Symfony’nin validator’ü, **PHP reflection** ve “getter” metodlarını kullanarak herhangi bir özelliğin değerini alır, bu nedenle bunlar public, private veya protected olabilir.

---

### 🧾 Validator Servisini Kullanma

Bir **Author** nesnesini doğrulamak için `validate()` metodunu kullanın.

Validator’ün görevi, bir sınıfın kurallarını okuyup, nesne üzerindeki verilerin bu kurallara uyup uymadığını kontrol etmektir.

Doğrulama başarısız olursa, boş olmayan bir hata listesi ( **ConstraintViolationList** ) döner.

Basit bir örnek:

```php
// ...
use App\Entity\Author;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

// ...
public function author(ValidatorInterface $validator): Response
{
    $author = new Author();

    // ... $author nesnesiyle ilgili işlemler

    $errors = $validator->validate($author);

    if (count($errors) > 0) {
        $errorsString = (string) $errors;
        return new Response($errorsString);
    }

    return new Response('The author is valid! Yes!');
}
```

Eğer `$name` özelliği boşsa, şu hata mesajını görürsünüz:

```
Object(App\Entity\Author).name:
    This value should not be blank.
```

Eğer `name` özelliğine bir değer girerseniz, başarı mesajı görünecektir.

Genellikle validator servisiyle doğrudan etkileşime girmezsiniz.

Doğrulama işlemi genellikle form verilerini işlerken dolaylı olarak yapılır.

Daha fazla bilgi için **Symfony form doğrulaması** konusuna bakın.

Hata koleksiyonunu bir template’e de iletebilirsiniz:

```php
if (count($errors) > 0) {
    return $this->render('author/validation.html.twig', [
        'errors' => $errors,
    ]);
}
```

Template içinde hataları istediğiniz şekilde görüntüleyebilirsiniz:

```twig
{# templates/author/validation.html.twig #}
<h3>The author has the following errors</h3>
<ul>
{% for error in errors %}
    <li>{{ error.message }}</li>
{% endfor %}
</ul>
```

Her doğrulama hatası (“constraint violation”), bir **ConstraintViolation** nesnesiyle temsil edilir.

Bu nesne, örneğin `ConstraintViolation::getConstraint()` metodu sayesinde, hataya neden olan constraint’e erişmenizi sağlar.

---

### 🔁 Validation Callables

Validation ayrıca bir closure oluşturarak değerleri bir dizi constraint’e göre doğrulamanıza izin verir (örneğin **Console** komut cevaplarını veya **OptionsResolver** değerlerini doğrularken kullanışlıdır):

* **createCallable()**

  Constraint’ler sağlanmadığında **ValidationFailedException** fırlatan bir closure döndürür.
* **createIsValidCallable()**

  Constraint’ler sağlanmadığında **false** döndüren bir closure döndürür.

---

### 🧱 Constraints

Validator, nesneleri constraint’lere (yani kurallara) göre doğrulamak için tasarlanmıştır.

Bir nesneyi doğrulamak için, sınıfına bir veya daha fazla constraint eşleyin ve sonra validator servisine gönderin.

Bir constraint, arka planda belirli bir koşulu doğrulayan bir PHP nesnesidir.

Gerçek hayatta “Kek yanmamalıdır” gibi bir ifade olabilir.

Symfony’de constraint’ler benzer şekilde bir koşulun doğru olduğunu iddia eder.

Bir değer verildiğinde, constraint bu değerin kurala uyup uymadığını söyler.

---

### 📚 Desteklenen Constraint’ler

Symfony, en sık kullanılan birçok constraint’i içerir:

#### 🧩 Temel Constraint’ler

Blank, IsFalse, IsNull, IsTrue, NotBlank, NotNull, Type

#### 🔤 String Constraint’leri

Charset, Cidr, CssColor, Email, ExpressionSyntax, Hostname, Ip, Json, Length, MacAddress, NoSuspiciousCharacters, NotCompromisedPassword, PasswordStrength, Regex, Twig, Ulid, Url, UserPassword, Uuid, WordCount, Yaml

#### ⚖️ Karşılaştırma Constraint’leri

DivisibleBy, EqualTo, GreaterThan, GreaterThanOrEqual, IdenticalTo, LessThan, LessThanOrEqual, NotEqualTo, NotIdenticalTo, Range, Unique

#### 🔢 Sayı Constraint’leri

Negative, NegativeOrZero, Positive, PositiveOrZero

#### 📅 Tarih Constraint’leri

Date, DateTime, Time, Timezone, Week

#### 🧠 Seçim Constraint’leri

Choice, Country, Language, Locale

#### 🖼️ Dosya Constraint’leri

File, Image

#### 💳 Finansal ve Diğer Sayı Constraint’leri

Bic, CardScheme, Currency, Iban, Isbn, Isin, Issn, Luhn

#### 🧩 Doctrine Constraint’leri

DisableAutoMapping, EnableAutoMapping, UniqueEntity

#### 🔧 Diğer Constraint’ler

All, AtLeastOneOf, Callback, Cascade, Collection, Compound, Count, Expression, GroupSequence, Sequentially, Traverse, Valid, When

Kendi özel constraint’lerinizi de oluşturabilirsiniz.

Bu konu **How to Create a Custom Validation Constraint** makalesinde anlatılmıştır.

---

### ⚙️ Constraint Yapılandırması

Bazı constraint’ler, **NotBlank** gibi basitken, bazıları (örneğin  **Choice** ) birkaç yapılandırma seçeneğine sahiptir.

Örneğin, Author sınıfının “fiction” veya “non-fiction” olabilen bir **genre** özelliği olduğunu varsayalım:

```php
// src/Entity/Author.php
namespace App\Entity;

// ...
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class Author
{
    private string $genre;

    // ...

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        // ...
        $metadata->addPropertyConstraint('genre', new Assert\Choice(
            choices: ['fiction', 'non-fiction'],
            message: 'Choose a valid genre.',
        ));
    }
}
```
