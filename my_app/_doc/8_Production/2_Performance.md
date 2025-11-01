# ⚡ Performans

Symfony, varsayılan haliyle hızlıdır. Ancak, aşağıdaki performans kontrol listelerinde açıklandığı gibi sunucunuzu ve uygulamanızı optimize ederek daha da hızlı hale getirebilirsiniz.

---

## 🧾 Performans Kontrol Listeleri

Uygulamanızın ve sunucunuzun maksimum performans için yapılandırıldığını doğrulamak amacıyla bu kontrol listelerini kullanın:

### ✅ Symfony Uygulama Kontrol Listesi

* Sunucunuz **APC** kullanıyorsa **APCu Polyfill** yükleyin
* Uygulamada etkinleştirilen **locale** sayısını sınırlayın

### ✅ Üretim Sunucusu Kontrol Listesi

* **Service container** ’ı tek bir dosyaya dökün
* **OPcache** byte code cache kullanın
* **OPcache** ’i maksimum performans için yapılandırın
* **PHP dosya zaman damgalarını** kontrol etmeyin
* **PHP realpath cache** ’i yapılandırın
* **Composer autoloader** ’ı optimize edin

---

## 🧩 Sunucunuz APC Kullanıyorsa APCu Polyfill Yükleyin

Üretim sunucunuz hâlâ eski **APC PHP** uzantısını **OPcache** yerine kullanıyorsa, uygulamanıza **APCu Polyfill** bileşenini yükleyin.

Bu, APCu PHP işlevleriyle uyumluluk sağlar ve **APCu Cache adapter** gibi gelişmiş Symfony özelliklerini etkinleştirir.

---

## 🌍 Uygulamada Etkin Locale Sayısını Sınırlayın

Sadece uygulamanızda gerçekten kullanılan çeviri dosyalarının oluşturulması için

`framework.enabled_locales` seçeneğini kullanın.

---

## 🧱 Service Container’ı Tek Bir Dosyaya Dökün

Symfony, varsayılan olarak service container’ı birden fazla küçük dosyaya derler.

Aşağıdaki parametreyi **true** olarak ayarlarsanız, container tek bir dosyada derlenir.

Bu, özellikle PHP 7.4 ve üzeri sürümlerde **class preloading** kullanırken performansı artırabilir:

```php
// config/services.php
namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return function(ContainerConfigurator $container): void {
    $container->parameters()->set('.container.dumper.inline_factories', true);
};
```

`.` ön eki, bu parametrenin yalnızca container derlemesi sırasında kullanıldığını gösterir.

Daha fazla bilgi için **Configuration Parameters** bölümüne bakın.

---

## 🧠 OPcache Byte Code Cache Kullanın

 **OPcache** , derlenmiş PHP dosyalarını saklayarak her istekte yeniden derleme ihtiyacını ortadan kaldırır.

PHP 5.5’ten itibaren OPcache, PHP ile birlikte gelir. Daha eski sürümler için en yaygın byte code cache,  **APC** ’dir.

---

## 🚀 OPcache Class Preloading Kullanın

PHP 7.4’ten itibaren OPcache, sınıfları başlatma sırasında derleyip yükleyebilir ve sunucu yeniden başlatılana kadar tüm istekler için kullanılabilir hale getirebilir.

Bu, performansı önemli ölçüde artırır.

Container derlemesi sırasında (ör. `cache:clear` komutu çalıştırıldığında), Symfony **var/cache/** dizininde ön yüklenmesi gereken sınıfların listesini içeren bir dosya oluşturur.

Bu dosyayı doğrudan kullanmak yerine, **Symfony Flex** kullanan projelerde oluşturulan **config/preload.php** dosyasını kullanın:

```ini
; php.ini
opcache.preload=/path/to/project/config/preload.php

; required for opcache.preload:
opcache.preload_user=www-data
```

Bu dosya eksikse, Symfony Flex tarifini güncellemek için şu komutu çalıştırın:

```bash
composer recipes:update symfony/framework-bundle
```

Hangi sınıfların önceden yüklenip yüklenmeyeceğini belirtmek için

**container.preload** ve **container.no_preload** service tag’lerini kullanın.

---

## ⚙️ OPcache’i Maksimum Performans İçin Yapılandırın

Varsayılan **OPcache** yapılandırması Symfony uygulamaları için uygun değildir.

Aşağıdaki ayarları yapmanız önerilir:

```ini
; php.ini
; Derlenmiş PHP dosyalarını saklamak için kullanılacak maksimum bellek
opcache.memory_consumption=256

; Cache’e kaydedilebilecek maksimum dosya sayısı
opcache.max_accelerated_files=20000
```

---

## ⏱️ PHP Dosya Zaman Damgalarını Kontrol Etmeyin

Üretim sunucularında PHP dosyaları yalnızca yeni bir sürüm dağıtıldığında değişmelidir.

Ancak OPcache, varsayılan olarak önbelleğe alınmış dosyaların değişip değişmediğini kontrol eder.

Bu kontrol, küçük de olsa bir ek yük getirir ve şu şekilde devre dışı bırakılabilir:

```ini
; php.ini
opcache.validate_timestamps=0
```

Her dağıtımdan sonra OPcache önbelleğini boşaltıp yeniden oluşturmanız gerekir.

Aksi halde, uygulamadaki güncellemeleri göremezsiniz.

PHP’de **CLI** ve **web** süreçleri aynı OPcache’i paylaşmadığı için, terminalden çalıştırılan bir komutla web sunucusunun OPcache’ini temizleyemezsiniz.

Aşağıdaki yöntemlerden birini kullanabilirsiniz:

* Web sunucusunu yeniden başlatın
* **apc_clear_cache()** veya **opcache_reset()** fonksiyonlarını web üzerinden çağırın
* **cachetool** aracını kullanarak CLI üzerinden APC veya OPcache’i yönetin

---

## 🗂️ PHP Realpath Cache’i Yapılandırın

Bir göreli yol, gerçek ve mutlak yola dönüştürüldüğünde PHP sonucu önbelleğe alır.

Birçok PHP dosyasını açan uygulamalar (örneğin Symfony projeleri) için en az şu değerler önerilir:

```ini
; php.ini
; Sonuçları saklamak için ayrılan maksimum bellek
realpath_cache_size=4096K

; Sonuçların saklanma süresi (saniye)
realpath_cache_ttl=600
```

**open_basedir** yapılandırma seçeneği etkinse PHP, realpath cache’i devre dışı bırakır.

---

## 🧭 Composer Autoloader’ı Optimize Edin

Geliştirme sırasında kullanılan class loader, yeni veya değişen sınıfları bulmak için optimize edilmiştir.

Üretim sunucularında PHP dosyaları yalnızca yeni bir sürüm dağıtıldığında değişir.

Bu nedenle Composer’ın autoloader’ını optimize ederek, tüm uygulamayı bir kez tarayıp tüm sınıfların konumlarını içeren optimize edilmiş bir “class map” oluşturabilirsiniz.

Bu, `vendor/composer/autoload_classmap.php` dosyasında saklanır.

Yeni class map’i oluşturmak (ve dağıtım sürecinizin bir parçası haline getirmek) için şu komutu çalıştırın:

```bash
composer dump-autoload --no-dev --classmap-authoritative
```

* `--no-dev`: yalnızca geliştirme ortamında gereken sınıfları hariç tutar.
* `--classmap-authoritative`: PSR-0 ve PSR-4 uyumlu sınıflar için bir class map oluşturur ve bu map’te bulunmayan sınıflar için dosya sistemini taramayı durdurur.

(Bkz.  **Composer’ın autoloader optimizasyonu** )

---

## 🐞 Debug Modunda Container’ı XML Olarak Dökümlemeyi Devre Dışı Bırakın

Debug modunda Symfony, tüm service container bilgilerini (servisler, argümanlar vb.) içeren bir XML dosyası üretir.

Bu dosya, **debug:container** ve **debug:autowiring** gibi komutlar tarafından kullanılır.

Container büyüdükçe bu dosyanın boyutu ve oluşturma süresi de artar.

Bu XML dosyasının sağladığı fayda performans kaybını karşılamıyorsa, aşağıdaki şekilde üretimini devre dışı bırakabilirsiniz:

```php
// config/services.php

// ...
$container->parameters()->set('debug.container.dump', false);
```


## [Profiling Symfony Applications](https://symfony.com/doc/current/performance.html#profiling-symfony-applications "Permalink to this headline")

### [Profiling with Blackfire](https://symfony.com/doc/current/performance.html#profiling-with-blackfire "Permalink to this headline")

[Blackfire](https://blackfire.io/docs/introduction?utm_source=symfony&utm_medium=symfonycom_docs&utm_campaign=performance) is the best tool to profile and optimize performance of Symfony applications during development, test and production. It's a commercial service, but provides a [full-featured demo](https://demo.blackfire.io/?utm_source=symfony&utm_medium=symfonycom_docs&utm_campaign=performance).

### [Profiling with Symfony Stopwatch](https://symfony.com/doc/current/performance.html#profiling-with-symfony-stopwatch "Permalink to this headline")

Symfony provides a basic performance profiler in the development [config environment](https://symfony.com/doc/current/configuration.html#configuration-environments). Click on the "time panel" of the [web debug toolbar](https://symfony.com/doc/current/page_creation.html#web-debug-toolbar) to see how much time Symfony spent on tasks such as making database queries and rendering templates.

You can measure the execution time and memory consumption of your own code and display the result in the Symfony profiler thanks to the [Stopwatch component](https://symfony.com/components/Stopwatch).

When using [autowiring](https://symfony.com/doc/current/service_container.html#services-autowire), type-hint any controller or service argument with the [Stopwatch](https://github.com/symfony/symfony/blob/7.3/src/Symfony/Component/Stopwatch/Stopwatch.php "Symfony\Component\Stopwatch\Stopwatch") class and Symfony will inject the `debug.stopwatch` service:

```
use Symfony\Component\Stopwatch\Stopwatch;

class DataExporter
{
    public function __construct(
        private Stopwatch $stopwatch,
    ) {
    }

    public function export(): void
    {
        // the argument is the name of the "profiling event"
        $this->stopwatch->start('export-data');

        // ...do things to export data...

        // reset the stopwatch to delete all the data measured so far
        // $this->stopwatch->reset();

        $this->stopwatch->stop('export-data');
    }
}
```

If the request calls this service during its execution, you'll see a new event called `export-data` in the Symfony profiler.

The `start()`, `stop()` and `getEvent()` methods return a [StopwatchEvent](https://github.com/symfony/symfony/blob/7.3/src/Symfony/Component/Stopwatch/StopwatchEvent.php "Symfony\Component\Stopwatch\StopwatchEvent") object that provides information about the current event, even while it's still running. This object can be converted to a string for a quick summary:

```
// ...
dump((string) $this->stopwatch->getEvent('export-data')); // dumps e.g. '4.50 MiB - 26 ms'
```

You can also profile your template code with the [stopwatch Twig tag](https://symfony.com/doc/current/reference/twig_reference.html#reference-twig-tag-stopwatch):

```
{% stopwatch 'render-blog-posts' %}
    {% for post in blog_posts %}
        {# ... #}
    {% endfor %}
{% endstopwatch %}
```

#### [Profiling Categories](https://symfony.com/doc/current/performance.html#profiling-categories "Permalink to this headline")

Use the second optional argument of the `start()` method to define the category or tag of the event. This helps keep events organized by type:

```
$this->stopwatch->start('export-data', 'export');
```

#### [Profiling Periods](https://symfony.com/doc/current/performance.html#profiling-periods "Permalink to this headline")

A [real-world stopwatch](https://en.wikipedia.org/wiki/Stopwatch) not only includes the start/stop button but also a "lap button" to measure each partial lap. This is exactly what the `lap()` method does, which stops an event and then restarts it immediately:

```
$this->stopwatch->start('process-data-records', 'export');

foreach ($records as $record) {
    // ... some code goes here
    $this->stopwatch->lap('process-data-records');
}

$event = $this->stopwatch->stop('process-data-records');
// $event->getDuration(), $event->getMemory(), etc.

// Lap information is stored as "periods" within the event:
// $event->getPeriods();

// Gets the last event period:
// $event->getLastPeriod();
```

7.2

The `getLastPeriod()` method was introduced in Symfony 7.2.

#### [Profiling Sections](https://symfony.com/doc/current/performance.html#profiling-sections "Permalink to this headline")

Sections are a way to split the profile timeline into groups. Example:

```
$this->stopwatch->openSection();
$this->stopwatch->start('validating-file', 'validation');
$this->stopwatch->stopSection('parsing');

$events = $this->stopwatch->getSectionEvents('parsing');

// later you can reopen a section passing its name to the openSection() method
$this->stopwatch->openSection('parsing');
$this->stopwatch->start('processing-file');
$this->stopwatch->stopSection('parsing');
```

All events that don't belong to any named section are added to the special section called `__root__`. This way you can get all stopwatch events, even if you don't know their names, as follows:

```
use Symfony\Component\Stopwatch\Stopwatch;

foreach($this->stopwatch->getSectionEvents(Stopwatch::ROOT) as $event) {
    echo (string) $event;
}
```

7.2

The `Stopwatch::ROOT` constant as a shortcut for `__root__` was introduced in Symfony 7.2.
