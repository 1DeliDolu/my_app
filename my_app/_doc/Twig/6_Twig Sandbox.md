### 🛡️ Twig Sandbox

 **Sandbox extension** , güvenilmeyen (untrusted) kodu çalıştırmak için kullanılır.

---

### ⚙️ Sandbox’ı Kaydetme

`addExtension()` metodu aracılığıyla `SandboxExtension` eklentisini kaydedin:

```php
$twig->addExtension(new \Twig\Extension\SandboxExtension($policy));
```

---

### 🧩 Sandbox Politikasını Yapılandırma

Sandbox güvenliği, `SandboxExtension` yapıcısına (constructor) geçirilmesi gereken bir **policy** (politika) nesnesi tarafından yönetilir.

Twig, varsayılan olarak bir adet politika sınıfı ile gelir:

`\Twig\Sandbox\SecurityPolicy`

Bu sınıf, belirli etiketlerin (tags), filtrelerin (filters), fonksiyonların (functions) ve nesne üzerindeki özelliklerin (properties) veya metotların (methods) kullanımına izin verilmesini sağlar:

```php
$tags = ['if'];
$filters = ['upper'];
$methods = [
    'Article' => ['getTitle', 'getBody'],
];
$properties = [
    'Article' => ['title', 'body'],
];
$functions = ['range'];

$policy = new \Twig\Sandbox\SecurityPolicy($tags, $filters, $methods, $properties, $functions);
```

Yukarıdaki yapılandırma ile güvenlik politikası yalnızca şu kullanımlara izin verir:

* `if` etiketi,
* `upper` filtresi,
* `Article` sınıfında `getTitle()` ve `getBody()` metotları,
* `Article` sınıfında `title` ve `body` özellikleri.

Bunların dışındaki tüm işlemler reddedilir ve bir `\Twig\Sandbox\SecurityError` istisnası fırlatılır.

> **Not:**
>
> Twig 3.14.1 (ve 3.11.2) itibarıyla, eğer `Article` sınıfı `ArrayAccess` arayüzünü uygularsa, şablonlar yalnızca `title` ve `body` niteliklerine erişebilir.
>
> Yerel dizi benzeri sınıflar (örneğin `ArrayObject`) ise her zaman otomatik olarak izinlidir, ayrıca yapılandırmanız gerekmez.

> **Dikkat:**
>
> `extends` ve `use` etiketleri sandbox modunda **her zaman** izinlidir.
>
> Ancak Twig 4.0 sürümünde bu davranış değişecek ve bu etiketlerin de açıkça izin listesine eklenmesi gerekecektir.

---

### 🔒 Sandbox’ı Etkinleştirme

Varsayılan olarak sandbox modu  **devre dışıdır** . Güvenilmeyen bir şablon dosyasını dahil ederken `sandboxed` seçeneğini kullanarak etkinleştirebilirsiniz:

```twig
{{ include('user.html.twig', sandboxed: true) }}
```

Tüm şablonları sandbox modunda çalıştırmak için, `SandboxExtension` yapıcısına ikinci parametre olarak `true` verin:

```php
$twig->addExtension(new \Twig\Extension\SandboxExtension($policy, true));
```

---

### ⚠️ Callable Argümanlarını Kabul Etmek

Twig sandbox, hangi fonksiyonların, filtrelerin, testlerin ve nokta (dot) işlemlerinin kullanılabileceğini yapılandırmanıza olanak tanır.

Ancak bu çağrılar argüman alabilir ve bu argümanlar sandbox tarafından doğrulanmaz. Bu nedenle **çok dikkatli olmanız gerekir.**

Örneğin, bir PHP  **callable** ’ı (çağrılabilir) argüman olarak kabul etmek tehlikelidir çünkü kullanıcı herhangi bir PHP fonksiyonunu (örneğin `system()`, `exec()`) çağırabilir:

```php
$twig->addFilter(new \Twig\TwigFilter('custom', function (callable $callable) {
    // ...
    $callable();
    // ...
}));
```

Bu güvenlik sorununu önlemek için `callable` yerine **`\Closure`** tipini kullanın.

Bu, yalnızca PHP closure’larının kabul edilmesini sağlar (Twig ok fonksiyonları dahil):

```php
$twig->addFilter(new \Twig\TwigFilter('custom', function (\Closure $callable) {
    // ...
    $callable();
    // ...
}));
```

Twig’de bu şekilde kullanılabilir:

```twig
{{ people|custom(p => p.username|join(', ')) }}
```

Herhangi bir PHP callable, **first-class callable** sözdizimi kullanılarak kolayca bir closure’a dönüştürülebilir.
