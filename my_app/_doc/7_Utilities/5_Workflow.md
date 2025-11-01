
#  Workflow

Sayfayı düzenle

Bir Symfony uygulamasında Workflow bileşenini kullanmak, önce workflow'lar ve state machine'ler hakkında bazı temel teorileri ve kavramları bilmeyi gerektirir. Hızlı bir genel bakış için bu makaleyi okuyun.

🔧 **Kurulum**

Symfony Flex kullanan uygulamalarda, Workflow özelliğini kullanmadan önce bu komutu çalıştırın:

```
composer require symfony/workflow
```

⚙️ **Yapılandırma**

Tüm yapılandırma seçeneklerini görmek için, bileşeni bir Symfony projesi içinde kullanıyorsanız bu komutu çalıştırın:

```
php bin/console config:dump-reference framework workflows
```

🏗️ **Bir Workflow Oluşturma**

Bir workflow, nesnelerinizin geçtiği bir süreç veya yaşam döngüsüdür. Süreçteki her adım veya aşama bir *place* olarak adlandırılır. Ayrıca, bir  *place* ’den diğerine geçmek için gereken eylemi tanımlayan  *transition* ’ları da tanımlarsınız.


![1761988407147](image/5_Workflow/1761988407147.png)


Bir grup *place* ve *transition* bir *definition* oluşturur. Bir workflow’un, bir  *Definition* ’a ve durumları nesnelere yazacak bir yola (örneğin bir *MarkingStoreInterface* örneğine) ihtiyacı vardır.

Aşağıdaki blog gönderisi örneğini düşünün. Bir gönderinin şu  *place* ’leri olabilir:  **draft** ,  **reviewed** ,  **rejected** ,  **published** . Workflow’u şu şekilde tanımlayabilirsiniz:

```php
// config/packages/workflow.php
use App\Entity\BlogPost;
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $blogPublishing = $framework->workflows()->workflows('blog_publishing');
    $blogPublishing
        ->type('workflow') // veya 'state_machine'
        ->supports([BlogPost::class])
        ->initialMarking(['draft']);

    $blogPublishing->auditTrail()->enabled(true);
    $blogPublishing->markingStore()
        ->type('method')
        ->property('currentPlace');

    // place'leri manuel olarak tanımlamak isteğe bağlıdır
    $blogPublishing->place()->name('draft');
    $blogPublishing->place()->name('reviewed');
    $blogPublishing->place()->name('rejected');
    $blogPublishing->place()->name('published');

    $blogPublishing->transition()
        ->name('to_review')
            ->from(['draft'])
            ->to(['reviewed']);

    $blogPublishing->transition()
        ->name('publish')
            ->from(['reviewed'])
            ->to(['published']);

    $blogPublishing->transition()
        ->name('reject')
            ->from(['reviewed'])
            ->to(['rejected']);
};
```

İlk workflow’larınızı oluşturuyorsanız, workflow içeriğini hata ayıklamak için `workflow:dump` komutunu kullanmayı düşünün.

YAML dosyalarında PHP sabitlerini `!php/const` gösterimiyle kullanabilirsiniz. Örneğin `'draft'` yerine `!php/const App\Entity\BlogPost::STATE_DRAFT` veya `'to_review'` yerine `!php/const App\Entity\BlogPost::TRANSITION_TO_REVIEW` kullanabilirsiniz.

Eğer *transitions* tanımlarınızda kullanılan tüm  *place* ’leri belirttiyseniz, *places* seçeneğini atlayabilirsiniz. Symfony,  *place* ’leri  *transition* ’lardan otomatik olarak çıkaracaktır.

🆕 **7.1**

*places* seçeneğini atlama desteği Symfony 7.1’de tanıtıldı.

Yapılandırılan  *property* , *marking store* tarafından getter/setter metodları aracılığıyla kullanılacaktır:

```php
// src/Entity/BlogPost.php
namespace App\Entity;

class BlogPost
{
    // yapılandırılmış marking store özelliği tanımlanmalıdır
    private string $currentPlace;
    private string $title;
    private string $content;

    // marking store tarafından erişim için getter/setter metodları gereklidir
    public function getCurrentPlace(): string
    {
        return $this->currentPlace;
    }

    public function setCurrentPlace(string $currentPlace, array $context = []): void
    {
        $this->currentPlace = $currentPlace;
    }

    // başlangıç işaretlemesini constructor veya başka bir metodda ayarlamanız gerekmez;
    // bu, workflow içinde 'initial_marking' seçeneğiyle yapılandırılır
}
```

Ayrıca *marking store* için public özellikler de kullanılabilir. Yukarıdaki sınıf şu hale gelir:

```php
// src/Entity/BlogPost.php
namespace App\Entity;

class BlogPost
{
    // yapılandırılmış marking store özelliği tanımlanmalıdır
    public string $currentPlace;
    public string $title;
    public string $content;
}
```

Public özellikler kullanıldığında *context* desteklenmez. *Context* desteğini eklemek için bir setter metodu tanımlamanız gerekir:

```php
// src/Entity/BlogPost.php
namespace App\Entity;

class BlogPost
{
    public string $currentPlace;
    // ...

    public function setCurrentPlace(string $currentPlace, array $context = []): void
    {
        // özelliği atayın ve context ile bir şey yapın
    }
}
```

*marking store type* “multiple_state” veya “single_state” olabilir.

Bir  *single state marking store* , bir modelin aynı anda birden fazla  *place* ’te olmasını desteklemez. Bu, bir “workflow”un “multiple_state” marking store kullanması ve bir “state_machine”in “single_state” marking store kullanması gerektiği anlamına gelir. Symfony, *type* değerine göre marking store’u varsayılan olarak yapılandırır, bu nedenle manuel olarak yapılandırmamak tercih edilir.

Bir *single state marking store* veriyi depolamak için bir string kullanır.

Bir *multiple state marking store* ise veriyi depolamak için bir array kullanır.

Eğer hiçbir marking store tanımlanmadıysa, her iki durumda da `null` döndürmeniz gerekir (örneğin yukarıdaki örnekte `App\Entity\BlogPost::getCurrentPlace(): ?array` veya `App\Entity\BlogPost::getCurrentPlace(): ?string` gibi).

`marking_store.type` (varsayılan değeri *type* değerine bağlıdır) ve `property` (varsayılan değeri `['marking']`) öznitelikleri isteğe bağlıdır. Eğer belirtilmezlerse varsayılan değerleri kullanılır. Varsayılan değeri kullanmanız şiddetle tavsiye edilir.

`audit_trail.enabled` seçeneğini `true` olarak ayarlamak, uygulamanın workflow etkinliği için ayrıntılı günlük mesajları üretmesini sağlar.

Bu **blog_publishing** adlı workflow ile, bir blog gönderisi üzerinde hangi eylemlere izin verildiğine karar vermenize yardımcı olabilirsiniz:

```php
use App\Entity\BlogPost;
use Symfony\Component\Workflow\Exception\LogicException;

$post = new BlogPost();
// başlangıç marking'ini kodla ayarlamanız gerekmez;
// bu, workflow içinde 'initial_marking' seçeneğiyle yapılandırılır

$workflow = $this->container->get('workflow.blog_publishing');
$workflow->can($post, 'publish'); // False
$workflow->can($post, 'to_review'); // True

// gönderi üzerindeki currentState'i güncelleyin
try {
    $workflow->apply($post, 'to_review');
} catch (LogicException $exception) {
    // ...
}

// gönderinin mevcut durumundaki tüm kullanılabilir geçişleri görün
$transitions = $workflow->getEnabledTransitions($post);
// mevcut durumda belirli bir geçişi görün
$transition = $workflow->getEnabledTransition($post, 'publish');
```

### 🔄 Multiple State Marking Store Kullanımı

Eğer bir *workflow* oluşturuyorsanız,  *marking store* ’unuzun aynı anda birden fazla *place* içermesi gerekebilir. Bu nedenle, Doctrine kullanıyorsanız, eşleşen sütun tanımının *json* türünü kullanması gerekir:

```php
// src/Entity/BlogPost.php
namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class BlogPost
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(type: Types::JSON)]
    private array $currentPlaces;

    // ...
}
```

*Marking store* için `simple_array` türünü kullanmamalısınız.

Bir *multiple state marking store* içinde  *place* ’ler, değeri `1` olan anahtarlar olarak saklanır, örneğin `['draft' => 1]`.

Eğer *marking store* yalnızca bir *place* içeriyorsa, bu Doctrine türü değeri yalnızca bir string olarak depolar ve nesnenin mevcut  *place* ’inin kaybolmasına neden olur.



### 🧩 Bir Sınıfta Workflow’a Erişim

Symfony, tanımladığınız her workflow için bir *service* oluşturur. Herhangi bir servis ya da controller içinde bu workflow’ları enjekte etmenin iki yolu vardır:

---

#### (1) 🔤 Belirli Bir Argüman Adı Kullanmak

Yapıcı metodunuzu ( *constructor* ) veya metod argümanınızı `WorkflowInterface` ile *type-hint* edin ve argüman adını şu desenle adlandırın:

👉 “workflow adının camelCase hali” + **Workflow** soneki.

Eğer *state machine* tipi kullanıyorsanız, **StateMachine** soneki kullanın.

Örneğin, daha önce tanımlanan **blog_publishing** workflow’unu enjekte etmek için:

```php
use App\Entity\BlogPost;
use Symfony\Component\Workflow\WorkflowInterface;

class MyClass
{
    public function __construct(
        private WorkflowInterface $blogPublishingWorkflow,
    ) {
    }

    public function toReview(BlogPost $post): void
    {
        try {
            // gönderi üzerindeki currentState'i güncelle
            $this->blogPublishingWorkflow->apply($post, 'to_review');
        } catch (LogicException $exception) {
            // ...
        }
        // ...
    }
}
```

---

#### (2) 🎯 #[Target] Özniteliğini Kullanmak

Aynı tipin birden fazla uygulamasıyla çalışırken, `#[Target]` özniteliği hangi servisin enjekte edileceğini seçmenizi sağlar. Symfony, her workflow ile aynı ada sahip bir *target* oluşturur.

Örneğin, daha önce tanımlanan **blog_publishing** workflow’unu seçmek için:

```php
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Workflow\WorkflowInterface;

class MyClass
{
    public function __construct(
        #[Target('blog_publishing')] private WorkflowInterface $workflow,
    ) {
    }

    // ...
}
```

Bir Workflow’un etkin  *transition* ’ını almak için `getEnabledTransition()` metodunu kullanabilirsiniz.

🆕 **Symfony 7.1**

`getEnabledTransition()` metodu Symfony 7.1’de tanıtıldı.

---

### 📚 Tüm Workflow’ları Elde Etme

Belgelendirme gibi amaçlarla tüm workflow’ları almak isterseniz, aşağıdaki etiketle tüm servisleri enjekte edebilirsiniz:

* `workflow`: tüm workflow ve state machine’ler
* `workflow.workflow`: yalnızca workflow’lar
* `workflow.state_machine`: yalnızca state machine’ler

Workflow metadata’ları, `metadata` anahtarı altında etiketlere eklenir; böylece elinizdeki workflow hakkında daha fazla bağlam ve bilgiye sahip olursunuz.

🆕 **Symfony 7.1**

Bu etiketlere eklenen yapılandırma Symfony 7.1’de tanıtıldı.

Kullanılabilir workflow servislerinin listesini görmek için şu komutu çalıştırabilirsiniz:

```
php bin/console debug:autowiring workflow
```

---

### ⚙️ Birden Fazla Workflow Enjekte Etme

Tüm workflow’ları *lazy-load* etmek ve ihtiyacınız olanı almak için `AutowireLocator` özniteliğini kullanın:

```php
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\DependencyInjection\ServiceLocator;

class MyClass
{
    public function __construct(
        // 'workflow' hem workflows hem de state machine'leri enjekte eder;
        // 'name', servisleri bu etiket özelliğiyle indekslemesini söyler
        #[AutowireLocator('workflow', 'name')]
        private ServiceLocator $workflows,
    ) {
    }

    public function someMethod(): void
    {
        // eğer constructor’da 'name' özelliğini kullanarak servisleri indekslediyseniz,
        // workflow'lara adlarıyla erişebilirsiniz; aksi halde tam servis adını
        // 'workflow.' önekiyle kullanmalısınız (ör. 'workflow.user_registration')
        $workflow = $this->workflows->get('user_registration');

        // ...
    }
}
```

Yalnızca workflow’ları veya yalnızca state machine’leri enjekte etmek de mümkündür:

```php
public function __construct(
    #[AutowireLocator('workflow.workflow', 'name')]
    private ServiceLocator $workflows,
    #[AutowireLocator('workflow.state_machine', 'name')]
    private ServiceLocator $stateMachines,
) {
}
```

---

### 🔔 Olayların (Events) Kullanımı

Workflow’larınızı daha esnek hale getirmek için `Workflow` nesnesini bir `EventDispatcher` ile oluşturabilirsiniz. Böylece:

* *Transitions* ’ı engellemek için olay dinleyicileri (ör. gönderi verilerine göre) ekleyebilir,
* Workflow işlemi gerçekleştiğinde ek aksiyonlar (ör. bildirim gönderme) yapabilirsiniz.

Her adımda sırasıyla üç olay tetiklenir:

1. Tüm workflow’lar için genel bir olay
2. İlgili workflow için özel bir olay
3. İlgili workflow ve belirli *transition* veya *place* adıyla özel bir olay

---

#### 🔄 Bir State Transition Başlatıldığında Olay Sırası

**1. workflow.guard**

Geçişin engellenip engellenmeyeceğini doğrular ( *guard events* ).

Üç olay tetiklenir:

```
workflow.guard
workflow.[workflow adı].guard
workflow.[workflow adı].guard.[transition adı]
```

**2. workflow.leave**

Nesne bir  *place* ’den ayrılmak üzeredir.

```
workflow.leave
workflow.[workflow adı].leave
workflow.[workflow adı].leave.[place adı]
```

**3. workflow.transition**

Nesne bu  *transition* ’dan geçmektedir.

```
workflow.transition
workflow.[workflow adı].transition
workflow.[workflow adı].transition.[transition adı]
```

**4. workflow.enter**

Nesne yeni bir  *place* ’e girmek üzeredir. Bu olay, *marking* güncellenmeden hemen önce tetiklenir.

```
workflow.enter
workflow.[workflow adı].enter
workflow.[workflow adı].enter.[place adı]
```

**5. workflow.entered**

Nesne yeni  *place* ’e girmiştir ve *marking* güncellenmiştir.

```
workflow.entered
workflow.[workflow adı].entered
workflow.[workflow adı].entered.[place adı]
```

**6. workflow.completed**

Nesne bu geçişi tamamlamıştır.

```
workflow.completed
workflow.[workflow adı].completed
workflow.[workflow adı].completed.[transition adı]
```

**7. workflow.announce**

Nesne için artık erişilebilir hale gelen her *transition* için tetiklenir.

```
workflow.announce
workflow.[workflow adı].announce
workflow.[workflow adı].announce.[transition adı]
```

Bir *transition* uygulandıktan sonra, *announce* olayı mevcut tüm  *transition* ’ları test eder. Bu, yoğun CPU veya veritabanı işlemleri varsa performansı etkileyebilir.

*Announce* olayına ihtiyacınız yoksa, bunu *context* kullanarak devre dışı bırakabilirsiniz:

```php
$workflow->apply($subject, $transitionName, [Workflow::DISABLE_ANNOUNCE_EVENT => true]);
```

Ayrıca, aynı *place* içinde kalan geçişler için bile *leaving* ve *entering* olayları tetiklenir.

Eğer  *marking* ’i şu şekilde başlatırsanız:

```php
$workflow->getMarking($object);
```

o zaman `workflow.[workflow_name].entered.[initial_place_name]` olayı, varsayılan *context* (`Workflow::DEFAULT_INITIAL_CONTEXT`) ile çağrılır.

---

### 📝 Örnek: Her “blog_publishing” Workflow’unun Leave Olayını Günlüğe Kaydetme

```php
// src/App/EventSubscriber/WorkflowLoggerSubscriber.php
namespace App\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Component\Workflow\Event\LeaveEvent;

class WorkflowLoggerSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    public function onLeave(Event $event): void
    {
        $this->logger->alert(sprintf(
            'Blog gönderisi (id: "%s") "%s" geçişini "%s" konumundan "%s" konumuna gerçekleştirdi',
            $event->getSubject()->getId(),
            $event->getTransition()->getName(),
            implode(', ', array_keys($event->getMarking()->getPlaces())),
            implode(', ', $event->getTransition()->getTos())
        ));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LeaveEvent::getName('blog_publishing') => 'onLeave',
            // tercihen olayı manuel olarak da belirtebilirsiniz:
            // 'workflow.blog_publishing.leave' => 'onLeave',
        ];
    }
}
```

Tüm yerleşik workflow olayları, `getName(?string $workflowName, ?string $transitionOrPlaceName)` metodunu tanımlar; böylece metinlerle uğraşmadan tam olay adını oluşturabilirsiniz.

Bu metodu kendi özel olaylarınızda da `EventNameTrait` aracılığıyla kullanabilirsiniz.

🆕 **Symfony 7.1**

`getName()` metodu Symfony 7.1’de tanıtıldı.

---

Eğer bazı dinleyiciler geçiş sırasında  *context* ’i güncelliyorsa, onu *marking* üzerinden alabilirsiniz:

```php
$marking = $workflow->apply($post, 'to_review');

// yeni değeri içerir
$marking->getContext();
```

---

Ayrıca, aşağıdaki öznitelikleri kullanarak bu olayları dinlemek de mümkündür:

* `AsAnnounceListener`
* `AsCompletedListener`
* `AsEnterListener`
* `AsEnteredListener`
* `AsGuardListener`
* `AsLeaveListener`
* `AsTransitionListener`

Bu öznitelikler, `AsEventListener` öznitelikleriyle aynı şekilde çalışır:

```php
class ArticleWorkflowEventListener
{
    #[AsTransitionListener(workflow: 'my-workflow', transition: 'published')]
    public function onPublishedTransition(TransitionEvent $event): void
    {
        // ...
    }

    // ...
}
```

Daha fazla kullanım için PHP öznitelikleriyle olay dinleyicilerinin nasıl tanımlandığına ilişkin belgelere başvurabilirsiniz.


### 🛡️ Guard Olayları

“Guard events” adı verilen özel olay türleri vardır. Bu olay dinleyicileri, her `Workflow::can()`, `Workflow::apply()` veya `Workflow::getEnabledTransitions()` çağrısında tetiklenir.

Guard olayları ile hangi  *transition* ’ların engellenip hangilerinin izin verileceğine karar vermek için özel mantık ekleyebilirsiniz.

**Guard event adlarının listesi:**

```
workflow.guard
workflow.[workflow adı].guard
workflow.[workflow adı].guard.[transition adı]
```

Aşağıdaki örnek, bir blog gönderisinin başlığı yoksa “reviewed” durumuna geçmesini engeller:

```php
// src/App/EventSubscriber/BlogPostReviewSubscriber.php
namespace App\EventSubscriber;

use App\Entity\BlogPost;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\GuardEvent;

class BlogPostReviewSubscriber implements EventSubscriberInterface
{
    public function guardReview(GuardEvent $event): void
    {
        /** @var BlogPost $post */
        $post = $event->getSubject();
        $title = $post->title;

        if (empty($title)) {
            $event->setBlocked(true, 'Bu blog gönderisi başlığa sahip olmadığı için reviewed olarak işaretlenemez.');
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.blog_publishing.guard.to_review' => ['guardReview'],
        ];
    }
}
```

---

### ⚙️ Hangi Olayların Tetikleneceğini Seçmek

Her *transition* sırasında hangi olayların tetikleneceğini kontrol etmek istiyorsanız, `events_to_dispatch` yapılandırma seçeneğini kullanabilirsiniz.

Bu seçenek **Guard olayları** için geçerli değildir; onlar her zaman tetiklenir:

```php
// config/packages/workflow.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    // ...

    $blogPublishing = $framework->workflows()->workflows('blog_publishing');

    // ...
    // bir veya birden fazla olay adı geçebilirsiniz
    $blogPublishing->eventsToDispatch([
        'workflow.leave',
        'workflow.completed',
    ]);

    // hiçbir olayın tetiklenmemesini isterseniz boş dizi geçin
    $blogPublishing->eventsToDispatch([]);

    // ...
};
```

Belirli bir *transition* uygulanırken özel bir olayı devre dışı bırakmak da mümkündür:

```php
use App\Entity\BlogPost;
use Symfony\Component\Workflow\Exception\LogicException;

$post = new BlogPost();

$workflow = $this->container->get('workflow.blog_publishing');

try {
    $workflow->apply($post, 'to_review', [
        Workflow::DISABLE_ANNOUNCE_EVENT => true,
        Workflow::DISABLE_LEAVE_EVENT => true,
    ]);
} catch (LogicException $exception) {
    // ...
}
```

Belirli bir *transition* için bir olayı devre dışı bırakmak, workflow yapılandırmasında belirtilen tüm olaylardan **öncelikli** olur.

Yani yukarıdaki örnekte `workflow.leave` olayı, yapılandırmada belirtilmiş olsa bile tetiklenmeyecektir.

**Kullanılabilir sabitler:**

```
Workflow::DISABLE_LEAVE_EVENT
Workflow::DISABLE_TRANSITION_EVENT
Workflow::DISABLE_ENTER_EVENT
Workflow::DISABLE_ENTERED_EVENT
Workflow::DISABLE_COMPLETED_EVENT
```

---

### 📦 Event Metodları

Her workflow olayı bir `Event` örneğidir. Bu, her olayın aşağıdaki bilgilere erişebildiği anlamına gelir:

* `getMarking()` → Workflow’un  *Marking* ’ini döndürür.
* `getSubject()` → Olayı tetikleyen nesneyi döndürür.
* `getTransition()` → Olayı tetikleyen  *Transition* ’ı döndürür.
* `getWorkflowName()` → Olayı tetikleyen workflow’un adını döndürür.
* `getMetadata()` → Metadata’yı döndürür.

 **Guard Event** ’ler için, ek metodlara sahip genişletilmiş bir `GuardEvent` sınıfı vardır:

* `isBlocked()` →  *Transition* ’ın engellenip engellenmediğini döndürür.
* `setBlocked()` → *Blocked* değerini ayarlar.
* `getTransitionBlockerList()` →  *TransitionBlockerList* ’i döndürür. (bkz. blocking transitions)
* `addTransitionBlocker()` → Bir *TransitionBlocker* örneği ekler.

---

### 🚫 Transition’ları Engelleme

Bir *transition* uygulanmadan önce geçerli olup olmadığını belirlemek için özel mantık çağırarak workflow’un yürütülmesini kontrol edebilirsiniz.

Bu özellik “guards” ile sağlanır ve iki şekilde kullanılabilir:

1. **Guard olaylarını dinleyerek**
2. **Transition için bir guard yapılandırma seçeneği tanımlayarak**

Bu seçeneğin değeri, ExpressionLanguage bileşeniyle oluşturulmuş herhangi bir geçerli ifade olabilir:

```php
// config/packages/workflow.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $blogPublishing = $framework->workflows()->workflows('blog_publishing');
    // ... önceki yapılandırma

    $blogPublishing->transition()
        ->name('to_review')
            // geçiş yalnızca mevcut kullanıcı ROLE_REVIEWER rolüne sahipse izin verilir
            ->guard('is_granted("ROLE_REVIEWER")')
            ->from(['draft'])
            ->to(['reviewed']);

    $blogPublishing->transition()
        ->name('publish')
            // veya "is_remember_me", "is_fully_authenticated", "is_granted"
            ->guard('is_authenticated')
            ->from(['reviewed'])
            ->to(['published']);

    $blogPublishing->transition()
        ->name('reject')
            // "subject" gönderiye atıfta bulunan herhangi bir geçerli ifade olabilir
            ->guard('is_granted("ROLE_ADMIN") and subject.isStatusReviewed()')
            ->from(['reviewed'])
            ->to(['rejected']);
};
```

---

### 💬 Transition Blocker Kullanımı

Bir  *transition* ’ı durdururken, kullanıcı dostu bir hata mesajı döndürmek için  *transition blocker* ’lar kullanabilirsiniz.

Bu örnekte mesaj, Event’in metadata’sından alınır, böylece metinleri merkezi olarak yönetebilirsiniz.

Basitleştirilmiş bir örnek aşağıdadır; üretim ortamında mesajları tek bir yerden yönetmek için **Translation** bileşenini kullanmak tercih edilir:

```php
// src/App/EventSubscriber/BlogPostPublishSubscriber.php
namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\TransitionBlocker;

class BlogPostPublishSubscriber implements EventSubscriberInterface
{
    public function guardPublish(GuardEvent $event): void
    {
        $eventTransition = $event->getTransition();
        $hourLimit = $event->getMetadata('hour_limit', $eventTransition);

        if (date('H') <= $hourLimit) {
            return;
        }

        // Yayın geçişini 20:00’den sonra engelle
        // ve kullanıcıya açıklayıcı mesaj göster
        $explanation = $event->getMetadata('explanation', $eventTransition);
        $event->addTransitionBlocker(new TransitionBlocker($explanation, '0'));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.blog_publishing.guard.publish' => ['guardPublish'],
        ];
    }
}
```


### 🧱 Kendi Marking Store’unuzu Oluşturma

*Marking* güncellendiğinde ek mantık yürütmeniz gerekiyorsa kendi  *store* ’unuzu uygulamanız gerekebilir.

Örneğin, belirli  *workflow* ’larda  *marking* ’in özel şekilde saklanması gerekebilir.

Bunu yapmak için `MarkingStoreInterface` arayüzünü uygulamalısınız:

```php
namespace App\Workflow\MarkingStore;

use Symfony\Component\Workflow\Marking;
use Symfony\Component\Workflow\MarkingStore\MarkingStoreInterface;

final class BlogPostMarkingStore implements MarkingStoreInterface
{
    /**
     * @param BlogPost $subject
     */
    public function getMarking(object $subject): Marking
    {
        return new Marking([$subject->getCurrentPlace() => 1]);
    }

    /**
     * @param BlogPost $subject
     */
    public function setMarking(object $subject, Marking $marking, array $context = []): void
    {
        $marking = key($marking->getPlaces());
        $subject->setCurrentPlace($marking);
    }
}
```

*Marking store* uygulamanızı oluşturduktan sonra, workflow’unuzu bunu kullanacak şekilde yapılandırabilirsiniz:

```php
// config/packages/workflow.php
use App\Workflow\MarkingStore\ReflectionMarkingStore;
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    // ...

    $blogPublishing = $framework->workflows()->workflows('blog_publishing');
    // ...

    $blogPublishing->markingStore()
        ->service(BlogPostMarkingStore::class);
};
```

---

### 🪶 Twig İçinde Kullanım

Symfony, şablonlarda  *workflow* ’ları yönetmek ve domain mantığını azaltmak için çeşitli Twig fonksiyonları tanımlar:

| Fonksiyon                          | Açıklama                                                                                  |
| ---------------------------------- | ------------------------------------------------------------------------------------------- |
| `workflow_can()`                 | Belirtilen nesnenin belirtilen geçişi yapıp yapamayacağını `true/false`döndürür. |
| `workflow_transitions()`         | Belirtilen nesne için etkin olan tüm*transition* ’ları içeren bir dizi döndürür.  |
| `workflow_transition()`          | Belirli bir*transition* ’ı döndürür.                                                 |
| `workflow_marked_places()`       | Nesnenin mevcut*marking* ’indeki*place*adlarını döndürür.                         |
| `workflow_has_marked_place()`    | Nesnenin marking’inde belirtilen durumun olup olmadığını kontrol eder.                 |
| `workflow_transition_blockers()` | Belirli bir*transition*için `TransitionBlockerList`döndürür.                        |

**Örnek kullanım:**

```twig
<h3>Actions on Blog Post</h3>
{% if workflow_can(post, 'publish') %}
    <a href="...">Publish</a>
{% endif %}
{% if workflow_can(post, 'to_review') %}
    <a href="...">Submit to review</a>
{% endif %}
{% if workflow_can(post, 'reject') %}
    <a href="...">Reject</a>
{% endif %}

{# Etkin geçişleri döngüyle listeleme #}
{% for transition in workflow_transitions(post) %}
    <a href="...">{{ transition.name }}</a>
{% else %}
    No actions available.
{% endfor %}

{# Nesnenin belirli bir place’te olup olmadığını kontrol etme #}
{% if workflow_has_marked_place(post, 'reviewed') %}
    <p>This post is ready for review.</p>
{% endif %}

{# Nesnede belirli bir place işaretli mi kontrol etme #}
{% if 'reviewed' in workflow_marked_places(post) %}
    <span class="label">Reviewed</span>
{% endif %}

{# Transition blocker mesajlarını listeleme #}
{% for blocker in workflow_transition_blockers(post, 'publish') %}
    <span class="error">{{ blocker.message }}</span>
{% endfor %}
```

---

### 🗂️ Metadata Saklama

İhtiyaç duyarsanız, workflow’larınızda,  *place* ’lerde ve  *transition* ’larda keyfi metadata saklayabilirsiniz.

Bu metadata sadece workflow’un başlığı olabileceği gibi, karmaşık nesneler de olabilir:

```php
// config/packages/workflow.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $blogPublishing = $framework->workflows()->workflows('blog_publishing');
    // ... önceki yapılandırma

    $blogPublishing->metadata([
        'title' => 'Blog Publishing Workflow'
    ]);

    // ...

    $blogPublishing->place()
        ->name('draft')
        ->metadata([
            'max_num_of_words' => 500,
        ]);

    // ...

    $blogPublishing->transition()
        ->name('to_review')
            ->from(['draft'])
            ->to(['reviewed'])
            ->metadata([
                'priority' => 0.5,
            ]);

    $blogPublishing->transition()
        ->name('publish')
            ->from(['reviewed'])
            ->to(['published'])
            ->metadata([
                'hour_limit' => 20,
                'explanation' => 'You can not publish after 8 PM.',
            ]);
};
```

---

### ⚙️ Metadata’ya Erişim

Metadata’ya controller içinde şu şekilde erişebilirsiniz:

```php
// src/App/Controller/BlogPostController.php
use App\Entity\BlogPost;
use Symfony\Component\Workflow\WorkflowInterface;
// ...

public function myAction(WorkflowInterface $blogPublishingWorkflow, BlogPost $post): Response
{
    $title = $blogPublishingWorkflow
        ->getMetadataStore()
        ->getWorkflowMetadata()['title'] ?? 'Default title'
    ;

    $maxNumOfWords = $blogPublishingWorkflow
        ->getMetadataStore()
        ->getPlaceMetadata('draft')['max_num_of_words'] ?? 500
    ;

    $aTransition = $blogPublishingWorkflow->getDefinition()->getTransitions()[0];
    $priority = $blogPublishingWorkflow
        ->getMetadataStore()
        ->getTransitionMetadata($aTransition)['priority'] ?? 0
    ;

    // ...
}
```

Tüm metadata türleriyle çalışabilen bir `getMetadata()` metodu da vardır:

```php
// workflow metadata
$title = $workflow->getMetadataStore()->getMetadata('title');

// place metadata
$maxNumOfWords = $workflow->getMetadataStore()->getMetadata('max_num_of_words', 'draft');

// transition metadata
$priority = $workflow->getMetadataStore()->getMetadata('priority', $aTransition);
```

Controller içinde bir flash mesajda da kullanılabilir:

```php
// $transition = ... (Transition örneği)

// $workflow enjekte edilmiş Workflow örneğidir
$title = $workflow->getMetadataStore()->getMetadata('title', $transition);
$this->addFlash('info', "You have successfully applied the transition with title: '$title'");
```

Metadata’ya bir *Listener* içinde, `Event` nesnesi aracılığıyla da erişebilirsiniz.

---

### 🪞 Twig’te Metadata

Twig şablonlarında metadata’ya `workflow_metadata()` fonksiyonu aracılığıyla erişebilirsiniz:

```twig
<h2>Metadata of Blog Post</h2>
<p>
    <strong>Workflow</strong>:<br>
    <code>{{ workflow_metadata(blog_post, 'title') }}</code>
</p>
<p>
    <strong>Current place(s)</strong>
    <ul>
        {% for place in workflow_marked_places(blog_post) %}
            <li>
                {{ place }}:
                <code>{{ workflow_metadata(blog_post, 'max_num_of_words', place) ?: 'Unlimited'}}</code>
            </li>
        {% endfor %}
    </ul>
</p>
<p>
    <strong>Enabled transition(s)</strong>
    <ul>
        {% for transition in workflow_transitions(blog_post) %}
            <li>
                {{ transition.name }}:
                <code>{{ workflow_metadata(blog_post, 'priority', transition) ?: 0 }}</code>
            </li>
        {% endfor %}
    </ul>
</p>
<p>
    <strong>to_review Priority</strong>
    <ul>
        <li>
            to_review:
            <code>{{ workflow_metadata(blog_post, 'priority', workflow_transition(blog_post, 'to_review')) }}</code>
        </li>
    </ul>
</p>
```


### ✅ Workflow Tanımlarını Doğrulama

Symfony, kendi özel mantığınızı kullanarak workflow tanımlarını doğrulamanıza olanak tanır.

Bunu yapmak için `DefinitionValidatorInterface` arayüzünü uygulayan bir sınıf oluşturun:

```php
namespace App\Workflow\Validator;

use Symfony\Component\Workflow\Definition;
use Symfony\Component\Workflow\Exception\InvalidDefinitionException;
use Symfony\Component\Workflow\Validator\DefinitionValidatorInterface;

final class BlogPublishingValidator implements DefinitionValidatorInterface
{
    public function validate(Definition $definition, string $name): void
    {
        if (!$definition->getMetadataStore()->getMetadata('title')) {
            throw new InvalidDefinitionException(sprintf('Workflow "%s" içinde metadata başlığı (title) eksik.', $name));
        }

        // ...
    }
}
```

Doğrulayıcınızı (validator) oluşturduktan sonra, workflow’unuzu bunu kullanacak şekilde yapılandırın:

```php
// config/packages/workflow.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $blogPublishing = $framework->workflows()->workflows('blog_publishing');
    // ...

    $blogPublishing->definitionValidators([
        App\Workflow\Validator\BlogPublishingValidator::class
    ]);

    // ...
};
```

 **BlogPublishingValidator** , workflow tanımını doğrulamak için container derlemesi (compilation) sırasında çalıştırılacaktır.

🆕 **Symfony 7.3**

Workflow tanım doğrulayıcıları (definition validators) desteği Symfony 7.3 sürümünde tanıtılmıştır.

---

### 📘 Daha Fazla Bilgi

* **Workflows and State Machines**
* **How to Dump Workflows**
