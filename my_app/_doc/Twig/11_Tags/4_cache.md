cache

3.2

cache etiketi Twig 3.2 sürümünde eklenmiştir.

cache etiketi, Twig’e bir şablon parçasını önbelleğe almasını söyler:

{% cache "cache key" %}

Önbelleğe alınan içerik (önbellek uygulamasına bağlı olarak kalıcı olabilir)

{% endcache %}

Önbelleğin belirli bir süre sonra sona ermesini istiyorsanız, ttl() değiştiricisi aracılığıyla saniye cinsinden bir süre belirtebilirsiniz:

{% cache "cache key" ttl(300) %}

300 saniye boyunca önbelleğe alınır

{% endcache %}

Önbellek anahtarı, şu ayrılmış karakterleri kullanmayan herhangi bir dize olabilir: {}()/@:;

İyi bir uygulama olarak, önbelleğin ne zaman yenilenmesi gerektiğinde otomatik olarak sona ermesini sağlayacak bazı yararlı bilgileri anahtarın içine gömmektir:

* Her önbelleğe benzersiz bir ad verin ve şablonlarınız gibi ad alanına (namespace) ayırın;
* Şablon kodu değiştiğinde artırdığınız bir tam sayı ekleyin (böylece mevcut tüm önbellekleri otomatik olarak geçersiz kılabilirsiniz);
* Şablon kodunda kullanılan değişkenler değiştiğinde güncellenen benzersiz bir anahtar ekleyin.

Örneğin, bir blog içeriği şablon parçasını önbelleğe almak için şunu kullanabilirsiniz:

{% cache "blog_post;v1;" ~ post.id ~ ";" ~ post.updated_at %}

Burada `blog_post` şablon parçasını tanımlar, `v1` şablon kodunun ilk sürümünü temsil eder, `post.id` blog gönderisinin kimliğini belirtir ve `post.updated_at` gönderinin en son ne zaman değiştirildiğini belirten zaman damgasını döndürür.

Bu tür bir önbellek anahtarı adlandırma stratejisi kullanmak, ttl kullanma ihtiyacını ortadan kaldırır. Bu, HTTP önbelleklerinde yaptığımız gibi “expiration” (sona erme) stratejisi yerine “validation” (doğrulama) stratejisi kullanmaya benzer.

Eğer önbellek uygulamanız etiketleri destekliyorsa, önbellek öğelerinizi etiketleyebilirsiniz:

{% cache "cache key" tags('blog') %}

Bazı kodlar

{% endcache %}

{% cache "cache key" tags(['cms', 'blog']) %}

Bazı kodlar

{% endcache %}

cache etiketi değişkenler için yeni bir “scope” (kapsam) oluşturur, yani yapılan değişiklikler yalnızca şablon parçasına özeldir:

{% set count = 1 %}

{% cache "cache key" tags('blog') %}

{# count değişkeninin değeri cache etiketi dışında etkilenmez #}

{% set count = 2 %}

Bazı kodlar

{% endcache %}

{# 1 görüntülenir #}

{{ count }}

Not

cache etiketi, varsayılan olarak kurulu olmayan CacheExtension uzantısının bir parçasıdır. Önce yükleyin:

$ composer require twig/cache-extra

Symfony projelerinde, twig/extra-bundle paketini yükleyerek bunu otomatik olarak etkinleştirebilirsiniz:

$ composer require twig/extra-bundle

Veya Twig ortamına uzantıyı açıkça ekleyin:

```php
use Twig\Extra\Cache\CacheExtension;

$twig = new \Twig\Environment(...);
$twig->addExtension(new CacheExtension());
```

Eğer Symfony kullanmıyorsanız, uzantı runtime’ını da kaydetmelisiniz:

```php
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Twig\Extra\Cache\CacheRuntime;
use Twig\RuntimeLoader\RuntimeLoaderInterface;

$twig->addRuntimeLoader(new class implements RuntimeLoaderInterface {
    public function load($class) {
        if (CacheRuntime::class === $class) {
            return new CacheRuntime(new TagAwareAdapter(new FilesystemAdapter()));
        }
    }
});
```
