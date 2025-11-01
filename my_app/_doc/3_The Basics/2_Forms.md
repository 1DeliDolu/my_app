# Formlar (Forms)

Symfony, HTML formlarının oluşturulması ve işlenmesi sürecini son derece kolaylaştıran güçlü bir **Form** bileşeni içerir.

Bu sistem; **HTML alanlarını oluşturma, doğrulama, veriyi nesnelere aktarma** gibi karmaşık işlemleri sizin yerinize yönetir.

---

## 🎥 Video Kaynakları

> **Video izlemeyi tercih ediyorsanız:** [Symfony Forms Screencast serisine](https://symfonycasts.com/) göz atabilirsiniz.

---

## ⚙️ Kurulum

Symfony Flex kullanan uygulamalarda form özelliğini kullanmadan önce şu komutla yükleyin:

```bash
composer require symfony/form
```

---

## 🧭 Kullanım Akışı

Symfony formlarını kullanırken önerilen üç adımlı yaklaşım şudur:

1. **Formu oluşturun**

   — Bunu bir **controller** içinde ya da özel bir **form sınıfı** tanımlayarak yapabilirsiniz.
2. **Formu şablonda (template)** render edin

   — Kullanıcının düzenleyip gönderebileceği HTML formu oluşturun.
3. **Formu işleyin**

   — Gönderilen veriyi doğrulayın, dönüştürün ve gerekli işlemi yapın (örneğin veritabanına kaydedin).

---

## 📝 Örnek Senaryo

Örneklerimizde küçük bir **"Todo List"** uygulaması geliştirdiğimizi varsayalım.

Kullanıcılar görev (task) ekleyip düzenleyebilecekler.

Her görev, şu **`Task`** sınıfıyla temsil edilir:

```php
// src/Entity/Task.php
namespace App\Entity;

class Task
{
    protected string $task;

    protected ?\DateTimeInterface $dueDate;

    public function getTask(): string
    {
        return $this->task;
    }

    public function setTask(string $task): void
    {
        $this->task = $task;
    }

    public function getDueDate(): ?\DateTimeInterface
    {
        return $this->dueDate;
    }

    public function setDueDate(?\DateTimeInterface $dueDate): void
    {
        $this->dueDate = $dueDate;
    }
}
```

Bu sınıf tamamen basit bir PHP nesnesidir ( **Plain Old PHP Object – POPO** ).

Symfony veya Doctrine ile doğrudan bağlantısı yoktur.

Ancak Doctrine entity’leriyle de  **aynı şekilde form oluşturup düzenleyebilirsiniz** .

---

## 🧩 Form Türleri (Form Types)

Symfony’de tüm form yapısı, **"form type"** (form türü) kavramı etrafında şekillenir.

Diğer framework’lerde genellikle **formlar** ve **form alanları** ayrı kavramlardır,

ancak Symfony’de her ikisi de birer **form type** olarak kabul edilir.

| HTML Elemanı                               | Symfony Form Type     | Açıklama                             |
| ------------------------------------------- | --------------------- | -------------------------------------- |
| `<input type="text">`                     | `TextType`          | Basit bir metin girişi alanı         |
| Birkaç HTML alanından oluşan adres formu | `PostalAddressType` | Grup formu (gömülü alanlar içerir) |
| Kullanıcı profili formu                   | `UserProfileType`   | Birden fazla alan içeren tam form     |

> 💡 Bu yapı sayesinde formlarınızı **kolayca bileşenlere ayırabilir** ve **iç içe (nested)** form yapıları kurabilirsiniz.

---

## 📦 Symfony’de Mevcut Form Türleri

Symfony, onlarca hazır form türüyle birlikte gelir:

örneğin `TextType`, `EmailType`, `ChoiceType`, `DateType`, `EntityType` vb.

Ayrıca **kendi özel form türlerinizi** de oluşturabilirsiniz.

---

## 🔍 Mevcut Form Türlerini Listeleme

Uygulamanızda kullanılabilir tüm form türlerini, genişletmeleri ve tahminleyicileri görmek için:

```bash
php bin/console debug:form
```

Belirli bir tür hakkında bilgi almak için:

```bash
php bin/console debug:form BirthdayType
```

Belirli bir türün özelliklerini görmek için:

```bash
php bin/console debug:form BirthdayType label_attr
```

---

## 🧠 Özet

| Konu                            | Açıklama                                                                              |
| ------------------------------- | --------------------------------------------------------------------------------------- |
| **Amaç**                 | Form oluşturmayı, doğrulamayı ve veriyi nesnelere dönüştürmeyi kolaylaştırmak |
| **Form Tipi (Form Type)** | Symfony’de her alan veya form, bir “form type” olarak temsil edilir                  |
| **Örnek Nesne**          | `Task`sınıfı – basit bir PHP nesnesi (veya Doctrine entity’si olabilir)          |
| **Kurulum**               | `composer require symfony/form`                                                       |
| **Komutlar**              | `debug:form`— mevcut form türlerini görüntüler                                   |

---

Symfony’nin Form bileşeni, basit HTML formlarını yönetmekten çok daha fazlasını sunar:

veri doğrulama, CSRF koruması, otomatik binding, nested form yapıları ve hatta API desteği ile

modern uygulamalarda **form yönetimini tamamen soyutlayan** güçlü bir sistem sağlar.


# Form Oluşturma (Building Forms)

Symfony, HTML formlarını tanımlamak için güçlü bir **Form Builder (Form Oluşturucu)** arayüzü sağlar.

Bu sistem, form alanlarını **nesne tabanlı (fluent interface)** bir yapı ile tanımlamanıza, sonrasında bu tanımları gerçek bir **Form nesnesine** dönüştürmenize olanak tanır.

---

## 🧱 Controller İçinde Form Oluşturma

Eğer controller’ınız `AbstractController` sınıfından türemişse, Symfony’nin sağladığı **`createFormBuilder()`** yardımcı metodunu kullanabilirsiniz:

```php
// src/Controller/TaskController.php
namespace App\Controller;

use App\Entity\Task;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TaskController extends AbstractController
{
    public function new(Request $request): Response
    {
        // örnek olarak bir Task nesnesi oluşturuluyor
        $task = new Task();
        $task->setTask('Write a blog post');
        $task->setDueDate(new \DateTimeImmutable('tomorrow'));

        $form = $this->createFormBuilder($task)
            ->add('task', TextType::class)
            ->add('dueDate', DateType::class)
            ->add('save', SubmitType::class, ['label' => 'Create Task'])
            ->getForm();

        // ...
    }
}
```

> Eğer controller `AbstractController`’dan türemiyorsa,
>
> `form.factory` servisini kullanarak `createBuilder()` metodunu çağırabilirsiniz.

Bu örnekte iki alan (`task`, `dueDate`) oluşturulmuş ve her biri uygun form tipiyle (`TextType`, `DateType`) eşlenmiştir.

Son olarak, özel bir etiket (`label`) ile bir gönderim butonu (`SubmitType`) eklenmiştir.

---

## 🧩 Form Sınıfları Oluşturma

Symfony, **controller’larda karmaşık form tanımlarını tutmamanızı** önerir.

Bunun yerine, **form sınıfları** oluşturmak daha iyi bir uygulamadır. Bu sayede:

* Kod daha temiz olur,
* Formlar birden fazla controller veya serviste tekrar kullanılabilir.

Form sınıfları, `FormTypeInterface` arayüzünü uygular; ancak genellikle `AbstractType` sınıfı genişletilerek yazılır:

```php
// src/Form/Type/TaskType.php
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('task', TextType::class)
            ->add('dueDate', DateType::class)
            ->add('save', SubmitType::class);
    }
}
```

> 💡 **İpucu:**
>
> Form sınıfı oluşturmayı kolaylaştırmak için `MakerBundle` kullanabilirsiniz:
>
> ```bash
> composer require --dev symfony/maker-bundle
> php bin/console make:form
> ```

---

## 🧠 Form Sınıfını Controller’da Kullanma

Form sınıfını oluşturduktan sonra controller’da şu şekilde kullanabilirsiniz:

```php
// src/Controller/TaskController.php
namespace App\Controller;

use App\Entity\Task;
use App\Form\Type\TaskType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class TaskController extends AbstractController
{
    public function new(): Response
    {
        $task = new Task();
        $task->setTask('Write a blog post');
        $task->setDueDate(new \DateTimeImmutable('tomorrow'));

        $form = $this->createForm(TaskType::class, $task);

        // ...
    }
}
```

---

## ⚙️ `data_class` Seçeneği ile Veri Nesnesini Belirtme

Symfony, `createForm()` metoduna gönderilen ikinci parametre (`$task`) üzerinden veri sınıfını tahmin etmeye çalışır.

Ancak **iç içe (nested)** form yapılarında bu yeterli olmaz.

Bu yüzden veri sınıfını açıkça belirtmek iyi bir uygulamadır:

```php
// src/Form/Type/TaskType.php
namespace App\Form\Type;

use App\Entity\Task;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\AbstractType;

class TaskType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }
}
```

---

## 🖼️ Formu Render Etme (Görselleştirme)

Controller’da oluşturulan formu Twig şablonuna gönderin:

```php
// src/Controller/TaskController.php
namespace App\Controller;

use App\Entity\Task;
use App\Form\Type\TaskType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TaskController extends AbstractController
{
    public function new(Request $request): Response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);

        return $this->render('task/new.html.twig', [
            'form' => $form,
        ]);
    }
}
```

Twig tarafında formu şu şekilde gösterebilirsiniz:

```twig
{# templates/task/new.html.twig #}
{{ form(form) }}
```

Bu kısa ifade:

* `<form>` etiketini başlatır ve bitirir,
* Tüm alanları (`task`, `dueDate`, `save`) otomatik render eder,
* Form metodunu `POST`, action’ı ise mevcut sayfa URL’si olarak ayarlar (isteğe bağlı olarak değiştirilebilir).

---

## 🔍 Symfony’nin Akıllı Veri Eşleme Özelliği

Symfony, `$task` nesnesindeki korumalı (`protected`) özelliklere erişmek için otomatik olarak

**getter** (`getTask()`) ve **setter** (`setTask()`) metodlarını kullanır.

Boolean tipindeki alanlar için `isPublished()` veya `hasReminder()` gibi "isser/hasser" metodları da desteklenir.

---

## 🎨 Form Temaları (Form Themes)

Symfony form sisteminde form görünümünü özelleştirmek için **form temaları** kullanılır.

Örneğin, formların **Bootstrap 5** ile uyumlu render edilmesi için şu yapılandırmayı ekleyin:

```php
// config/packages/twig.php
use Symfony\Config\TwigConfig;

return static function (TwigConfig $twig): void {
    $twig->formThemes(['bootstrap_5_layout.html.twig']);
};
```

Symfony’nin yerleşik form temaları:

* `bootstrap_3_layout.html.twig`
* `bootstrap_4_layout.html.twig`
* `bootstrap_5_layout.html.twig`
* `foundation_5_layout.html.twig`
* `foundation_6_layout.html.twig`
* `tailwind_2_layout.html.twig`

Ayrıca kendi form temanızı da oluşturabilirsiniz.

---

## 🧩 Alanları Parçalı Render Etme

Daha fazla kontrol istiyorsanız, her alanı parça parça render edebilirsiniz:

```twig
{{ form_start(form) }}
    {{ form_row(form.task) }}
    {{ form_row(form.dueDate) }}
    {{ form_row(form.save) }}
{{ form_end(form) }}
```

Alternatif olarak sadece belirli bölümleri (etiket, hata, input) gösterebilirsiniz:

```twig
{{ form_label(form.task) }}
{{ form_widget(form.task) }}
{{ form_errors(form.task) }}
```

---

## 🧠 Özet

| Adım                                | Açıklama                                                                    |
| ------------------------------------ | ----------------------------------------------------------------------------- |
| **Form oluşturma**            | `createFormBuilder()`veya özel form sınıfı (`TaskType`) ile yapılır |
| **Veri sınıfı belirtme**    | `configureOptions()`içinde `data_class`ile tanımlanır                  |
| **Render etme**                | Twig’de `{{ form(form) }}`veya `form_row()`fonksiyonlarıyla yapılır   |
| **Form temaları**             | Bootstrap, Foundation, Tailwind veya özel tema desteği mevcuttur            |
| **Getter/Setter zorunluluğu** | Symfony, korumalı özelliklere erişim için bu metodları kullanır         |

---

Symfony’nin form sistemi; doğrulama, veri bağlama, CSRF koruması,

tema desteği ve form tiplerini bileşenleştirme olanaklarıyla,

basit HTML formları yerine **güçlü, yeniden kullanılabilir form mimarisi** sunar.


# Formların İşlenmesi (Processing Forms)

Symfony’de formları işlerken önerilen yöntem, **formu oluşturma ve gönderimini tek bir action (controller metodu)** içinde yönetmektir.

Ayrı ayrı action’lar da kullanılabilir, ancak **tek bir işlem akışı** kodunuzu daha sade, okunabilir ve sürdürülebilir hale getirir.

---

## 🧠 Form İşleme Mantığı

Form işleme (processing), **kullanıcının gönderdiği verileri PHP nesnesinin (örneğin `Task`) özelliklerine aktarma** sürecidir.

Symfony bunu sizin yerinize yapar — tek yapmanız gereken formu `handleRequest()` ile isteğe bağlamaktır.

---

### 🧩 Örnek: Form Oluşturma ve İşleme

```php
// src/Controller/TaskController.php
namespace App\Controller;

use App\Entity\Task;
use App\Form\Type\TaskType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TaskController extends AbstractController
{
    public function new(Request $request): Response
    {
        // Yeni bir Task nesnesi oluştur (örnek veriyi kaldırdık)
        $task = new Task();

        // Formu oluştur
        $form = $this->createForm(TaskType::class, $task);

        // Kullanıcı isteğini (Request) işle
        $form->handleRequest($request);

        // Form gönderilmiş ve doğrulanmış mı kontrol et
        if ($form->isSubmitted() && $form->isValid()) {
            // Gönderilen veriler form nesnesinde mevcut
            // $task nesnesi de bu verilere göre otomatik güncellendi
            $task = $form->getData();

            // Örneğin: Görevi veritabanına kaydet
            // $entityManager = $this->getDoctrine()->getManager();
            // $entityManager->persist($task);
            // $entityManager->flush();

            // Başarılı işlemden sonra yönlendirme yap
            return $this->redirectToRoute('task_success');
        }

        // Form ilk kez açılıyorsa veya hatalıysa render et
        return $this->render('task/new.html.twig', [
            'form' => $form,
        ]);
    }
}
```

---

## 🔄 Bu Controller’ın 3 Durumu

### 🟢 1. **Sayfa ilk açıldığında**

* Form **henüz gönderilmemiştir** (`isSubmitted()` → false).
* Symfony, formu oluşturur ve Twig üzerinden görüntüler.

### 🟡 2. **Form gönderildi, fakat doğrulama hatası varsa**

* `handleRequest()` formun gönderildiğini algılar.
* Kullanıcı verilerini `$task` nesnesine yazar (`task` ve `dueDate` özellikleri güncellenir).
* Form doğrulaması yapılır (`isValid()` → false).
* Hatalar tespit edilirse form yeniden render edilir, bu kez  **hata mesajlarıyla birlikte** .

> 🧩 **Not:**
>
> `$form` nesnesini doğrudan `render()` metoduna geçirirseniz, Symfony yanıt kodunu otomatik olarak
>
> **HTTP 422 – Unprocessable Content** olarak ayarlar.
>
> Bu, **Symfony UX Turbo** gibi HTTP standardına dayalı araçlarla uyumluluk sağlar.

### 🔵 3. **Form geçerli şekilde gönderildiğinde**

* `isValid()` → true
* Symfony, form verilerini yeniden `$task` nesnesine yazar.
* Artık bu nesne ile işlem yapabilirsiniz (örneğin veritabanına kaydetmek).
* Ardından, kullanıcıyı başka bir sayfaya yönlendirirsiniz (örneğin bir “başarı” sayfasına).

> 🔁 **Neden yönlendirme yapılır?**
>
> Kullanıcı formu gönderdikten sonra sayfayı yenilerse, **veriler tekrar gönderilmesin** diye (POST/Redirect/GET deseni).

---

## 🧩 Alternatif: `submit()` Metodu ile Manuel İşleme

Bazı durumlarda formun **ne zaman gönderildiğini** veya **hangi verilerin gönderildiğini** manuel kontrol etmek isteyebilirsiniz.

Bu durumda `submit()` metodunu kullanabilirsiniz:

```php
$form = $this->createForm(TaskType::class, $task);

// Özel veri kaynağı (örneğin JSON API veya AJAX isteği)
$data = ['task' => 'New Task', 'dueDate' => '2025-01-01'];

// Veriyi manuel olarak forma gönder
$form->submit($data);
```

Bu yöntem, **REST API’lerde** veya **manuel form kontrolü gereken** özel durumlarda kullanılır.

---

## 🧠 Özet

| Adım                                                      | Açıklama                                                                       |
| ---------------------------------------------------------- | -------------------------------------------------------------------------------- |
| **1. Formu oluştur**                                | `createForm()`ile form nesnesi yaratılır                                     |
| **2. İsteği işle**                                | `$form->handleRequest($request)`formun gönderilip gönderilmediğini algılar |
| **3. Geçerliliği kontrol et**                      | `isSubmitted()`ve `isValid()`metodlarıyla kontrol yapılır                 |
| **4. Veriyi işle**                                  | `$form->getData()`ile formdan gelen veriyi alın, gerekirse kaydedin           |
| **5. Başarılı işlemin ardından yönlendir**     | `redirectToRoute()`ile tekrar gönderimlerin önüne geçilir                  |
| **6. Hatalı veya ilk açılışta formu render et** | `return $this->render('form.html.twig', ['form' => $form])`                    |

---

Symfony form sistemi, kullanıcıdan gelen verileri güvenli bir şekilde nesnelere aktarıp doğrulamanın en etkili yoludur.

Bu yaklaşım sayesinde form işlemleri  **tek bir controller metodunda** ,

hem **gösterme** hem de **işleme** aşamalarıyla birlikte yönetilir.


# İstemci Tarafı HTML Doğrulaması (Client-Side HTML Validation)

HTML5 sayesinde modern tarayıcılar, belirli doğrulama kurallarını **istemci tarafında** (client-side) doğal olarak uygulayabilir.

En yaygın doğrulama biçimi, form alanlarına `required` özniteliği eklenmesidir.

Bu durumda tarayıcı, kullanıcı formu boş bırakarak göndermeye çalıştığında **yerleşik bir uyarı mesajı** gösterir.

Symfony’nin form sistemi, bu HTML5 doğrulama özelliklerinden **tam olarak yararlanır**

ve form oluştururken uygun HTML özniteliklerini otomatik olarak ekler.

---

## 🚫 Doğrulamayı Devre Dışı Bırakmak

Bazen istemci tarafı doğrulama test sürecinde **engel oluşturabilir**

(örneğin, boş bir alanla sunucu tarafı doğrulamayı test etmek istediğinizde).

Bu durumda doğrulamayı devre dışı bırakabilirsiniz:

```twig
{# templates/task/new.html.twig #}
{{ form_start(form, {'attr': {'novalidate': 'novalidate'}}) }}
    {{ form_widget(form) }}
{{ form_end(form) }}
```

* `<form>` etiketine `novalidate` eklenirse, tüm istemci tarafı doğrulama devre dışı kalır.
* Sadece bir düğmede (örneğin “Kaydetmeden Geç”) istemci doğrulamasını devre dışı bırakmak isterseniz `formnovalidate` kullanılabilir.

---

## 🧩 Form Tipi Tahmini (Form Type Guessing)

Eğer formun yönettiği nesne üzerinde **doğrulama kuralları (validation constraints)** tanımlıysa,

Symfony bu metaveriyi analiz ederek **form alan türlerini otomatik olarak tahmin eder.**

Örneğin:

* `task` alanı için `TextType`
* `dueDate` alanı için `DateType` otomatik olarak seçilebilir.

Bunu etkinleştirmek için `add()` metodunun ikinci argümanını belirtmeyin veya `null` olarak geçin:

```php
// src/Form/Type/TaskType.php
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // tür belirtilmezse Symfony otomatik tahmin eder
            ->add('task')
            // tür belirtilmeden seçenek verilirse ikinci parametre null olmalıdır
            ->add('dueDate', null, ['required' => false])
            ->add('save', SubmitType::class);
    }
}
```

> 💡 Form bir **validation group** ile çalışıyorsa bile, tür tahmini tüm doğrulama kurallarını göz önünde bulundurur.

---

## ⚙️ Form Tipi Seçeneklerinin Tahmini (Form Type Options Guessing)

Form tipi tahmin mekanizması etkinleştirildiğinde Symfony sadece alan tipini değil,

bazı yaygın **seçenekleri (options)** de otomatik olarak belirler:

| Seçenek                | Nasıl Tahmin Edilir                                                                       | Kaynak                            |
| ----------------------- | ------------------------------------------------------------------------------------------ | --------------------------------- |
| **`required`**  | Alan `NotBlank`veya `NotNull`kuralına sahipse `true`olarak ayarlanır.              | Validation veya Doctrine metadata |
| **`maxlength`** | `Length`veya `Range`kısıtlamalarından ya da Doctrine sütun uzunluğundan alınır. | Validation veya Doctrine metadata |

Bu özellik sayesinde, istemci tarafı doğrulama **doğrudan sunucu tarafı kurallarıyla uyumlu** hale gelir.

Bir tahmin değerini değiştirmek istiyorsanız, doğrudan seçeneklerde geçersiz kılabilirsiniz:

```php
->add('task', null, ['attr' => ['maxlength' => 4]])
```

> 🧠 Doctrine entity’leri kullanıyorsanız, Symfony sadece form tiplerini değil,
>
> aynı zamanda **doğrulama kurallarını da** metadata’dan tahmin eder.
>
> Ayrıntılar için [Databases and Doctrine ORM](https://chatgpt.com/g/g-p-6904ef4ae8fc81918bdb521301b0c9c6-symfony/c/69051eea-a120-832c-93fc-ccb0d81abb74#) rehberine bakın.

---

## 🧱 Haritalanmamış Alanlar (Unmapped Fields)

Bir nesneyi form aracılığıyla düzenlerken, form üzerindeki her alan varsayılan olarak

nesnenin bir özelliğine karşılık gelir.

Eğer formda, entity üzerinde bulunmayan bir alan tanımlarsanız hata alırsınız.

Ancak, bazı durumlarda (örneğin “Kullanım koşullarını kabul ediyorum” onayı gibi)

nesneye bağlı olmayan alanlara ihtiyaç duyabilirsiniz.

Bu durumda `mapped` seçeneğini `false` olarak ayarlayın:

```php
// src/Form/Type/TaskType.php
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('task')
            ->add('dueDate')
            ->add('agreeTerms', CheckboxType::class, ['mapped' => false])
            ->add('save', SubmitType::class);
    }
}
```

Bu “unmapped” alanlara controller içinde şu şekilde erişebilirsiniz:

```php
$form->get('agreeTerms')->getData(); // değeri alır
$form->get('agreeTerms')->setData(true); // değeri ayarlar
```

> Ayrıca, gönderilen veride bulunmayan form alanları Symfony tarafından otomatik olarak `null` olarak ayarlanır.

---

## 💡 Formların Temel Amacı

Symfony form sisteminin iki temel görevi vardır:

1. **Nesnedeki veriyi** kullanıcıya sunulacak **HTML forma dönüştürmek**

   — (örneğin `Task` → HTML `<input>` alanları)
2. **Kullanıcının gönderdiği veriyi** tekrar **nesneye uygulamak**

   — (`POST` verisi → `Task` nesnesine aktarım)

Bu iki yönlü veri aktarımı, Symfony form yapısının özünü oluşturur.

---

## 📚 Daha Fazla Öğrenin

Symfony formları çok güçlüdür ve aşağıdaki konularda daha derin özellikler sunar:

### 🔍 Referanslar

* [Form Types Reference](https://symfony.com/doc/current/reference/forms/types.html)

### ⚡ Gelişmiş Özellikler

* [How to Upload Files](https://symfony.com/doc/current/controller/upload_file.html)
* [How to Implement CSRF Protection](https://symfony.com/doc/current/security/csrf.html)
* [How to Create a Custom Form Field Type](https://symfony.com/doc/current/form/create_custom_field_type.html)
* [How to Use Data Transformers](https://symfony.com/doc/current/form/data_transformers.html)
* [When and How to Use Data Mappers](https://symfony.com/doc/current/form/data_mappers.html)

### 🎨 Form Temaları ve Özelleştirme

* [Bootstrap 4 / 5 Form Themes](https://symfony.com/doc/current/form/bootstrap5.html)
* [Tailwind CSS Form Theme](https://symfony.com/doc/current/form/tailwindcss.html)
* [How to Customize Form Rendering](https://symfony.com/doc/current/form/form_customization.html)

### 🧩 Olaylar

* [Form Events](https://symfony.com/doc/current/form/events.html)
* [How to Dynamically Modify Forms Using Form Events](https://symfony.com/doc/current/form/dynamic_form_modification.html)

### ✅ Doğrulama

* [Configuring Validation Groups in Forms](https://symfony.com/doc/current/form/validation_groups.html)
* [How to Disable Validation of Submitted Data](https://symfony.com/doc/current/form/disable_validation.html)

### 🧠 Diğer Yararlar

* [How to Embed Forms and Collections](https://symfony.com/doc/current/form/form_collections.html)
* [How to Reduce Code Duplication with inherit_data](https://symfony.com/doc/current/form/inherit_data_option.html)
* [How to Unit Test Forms](https://symfony.com/doc/current/form/unit_testing.html)

---

> 📄 Bu belge ve kod örnekleri, **Creative Commons BY-SA 3.0** lisansı altında sunulmuştur.
>
