## Büyük Resim

**Sayfayı Düzenle**

Symfony’yi sadece 10 dakikada kullanmaya başlayın! Gerçekten! En önemli kavramları anlamak ve gerçek bir proje oluşturmaya başlamak için ihtiyacınız olan tek şey bu!

Daha önce bir web framework kullandıysanız, Symfony size oldukça tanıdık gelecektir. Eğer kullanmadıysanız, web uygulamaları geliştirmenin tamamen yeni bir yoluna hoş geldiniz. Symfony, en iyi uygulamaları benimser, geriye dönük uyumluluğu korur (Evet! Yükseltmeler her zaman güvenli ve kolaydır!) ve uzun vadeli destek sunar.

### Symfony’yi İndirme

Öncelikle, Composer’ın yüklü olduğundan ve PHP 8.1 veya daha yeni bir sürüm kullandığınızdan emin olun.

Hazır mısınız? Terminalde şu komutu çalıştırın:

```
composer create-project symfony/skeleton quick_tour
```

Bu komut, küçük ama güçlü bir Symfony uygulaması içeren yeni bir **quick_tour/** dizini oluşturur:

```
quick_tour/
├─ .env
├─ bin/console
├─ composer.json
├─ composer.lock
├─ config/
├─ public/index.php
├─ src/
├─ symfony.lock
├─ var/
└─ vendor/
```

Projeyi hemen tarayıcıda yükleyebilir miyiz? Evet! **Nginx** veya **Apache** kurabilir ve belge kök dizinini **public/** olarak yapılandırabilirsiniz.

Ancak geliştirme için, **Symfony CLI** aracını yüklemek ve yerel web sunucusunu şu şekilde çalıştırmak daha iyidir:

```
symfony server:start
```

Yeni uygulamanızı tarayıcıda **[http://localhost:8000](http://localhost:8000/)** adresine giderek deneyin!


## Temeller: Route, Controller, Response

Projemizde yalnızca yaklaşık 15 dosya var, ancak şık bir API, sağlam bir web uygulaması veya bir mikroservis olmaya hazır. Symfony küçük başlar, ancak sizinle birlikte ölçeklenir.

Ama çok ileri gitmeden önce, ilk sayfamızı oluşturarak temellere inelim.

### İlk Controller’ı Oluşturma

`src/Controller` içinde yeni bir **DefaultController** sınıfı ve içinde bir **index** metodu oluşturun:

```php
// src/Controller/DefaultController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DefaultController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return new Response('Hello!');
    }
}
```

Bu kadar! Ana sayfaya gidin: **[http://localhost:8000/](http://localhost:8000/)**

Symfony, URL’nin route ile eşleştiğini görür ve ardından yeni `index()` metodunu çalıştırır.

Bir  **controller** , yalnızca tek bir kurala sahip normal bir fonksiyondur:

Bir **Symfony Response** nesnesi döndürmelidir.

Ama bu response her şeyi içerebilir: basit bir metin, JSON ya da tam bir HTML sayfası.

### Route’u Daha İlginç Hale Getirelim

Routing sistemi çok daha güçlüdür. Route’u biraz değiştirelim:

```php
// src/Controller/DefaultController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DefaultController
{
    #[Route('/hello/{name}', name: 'index')]
    public function index(): Response
    {
        return new Response('Hello!');
    }
}
```

Bu sayfanın URL’si değişti: artık **/hello/** ile başlıyor.

`{name}` bir joker karakter (wildcard) gibi davranır ve herhangi bir değeri eşleştirir.

Dahası da var! Controller’ı da güncelleyin:

```php
<?php
// src/Controller/DefaultController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DefaultController
{
    #[Route('/hello/{name}', name: 'index')]
    public function index(string $name): Response
    {
        return new Response("Hello $name!");
    }
}
```

Sayfayı deneyin: **[http://localhost:8000/hello/Symfony](http://localhost:8000/hello/Symfony)**

Ekranda **Hello Symfony!** yazısını görmelisiniz.

URL’deki `{name}` değeri, controller’daki `$name` argümanı olarak kullanılabilir hale gelir.

### Yeni Sayfalar Eklemek

Attribute kullanarak, route ve controller yan yana bulunur.

Yeni bir sayfa mı gerekiyor?

**DefaultController** içine yeni bir route ve metot ekleyin:

```php
// src/Controller/DefaultController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DefaultController
{
    // ...

    #[Route('/simplicity', methods: ['GET'])]
    public function simple(): Response
    {
        return new Response('Simple! Easy! Great!');
    }
}
```

Routing bundan çok daha fazlasını yapabilir, ama bunu başka bir zamana bırakalım!

Şu anda uygulamamızın daha fazla özelliğe ihtiyacı var — bir şablon motoru, logging, hata ayıklama araçları ve daha fazlası gibi.
