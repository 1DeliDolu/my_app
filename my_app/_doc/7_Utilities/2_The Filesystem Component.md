## 📂 Filesystem Bileşeni

Filesystem bileşeni, dosya sistemi işlemleri ve dosya/dizin yollarının işlenmesi için platformdan bağımsız yardımcı araçlar sağlar.

### 🧩 Kurulum

```
composer require symfony/filesystem
```

Bu bileşeni bir Symfony uygulaması dışında yüklerseniz, Composer tarafından sağlanan sınıf otomatik yükleme mekanizmasını etkinleştirmek için kodunuzda `vendor/autoload.php` dosyasını çağırmanız gerekir. Daha fazla bilgi için bu makaleyi okuyun.

### ⚙️ Kullanım

Bileşen, `Filesystem` ve `Path` adında iki ana sınıf içerir:

```php
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

$filesystem = new Filesystem();

try {
    $filesystem->mkdir(
        Path::normalize(sys_get_temp_dir().'/'.random_int(0, 1000)),
    );
} catch (IOExceptionInterface $exception) {
    echo "Dizininiz oluşturulurken bir hata oluştu: ".$exception->getPath();
}
```

---

## 🛠️ Filesystem Yardımcıları

### 📁 mkdir

`mkdir()` bir dizini özyinelemeli olarak oluşturur. POSIX dosya sistemlerinde dizinler varsayılan olarak `0777` mod değeriyle oluşturulur. Kendi modunuzu ikinci argümanla ayarlayabilirsiniz:

```php
$filesystem->mkdir('/tmp/photos', 0700);
```

İlk argüman olarak bir dizi veya herhangi bir `Traversable` nesnesi geçebilirsiniz.

Bu işlev, zaten mevcut olan dizinleri yok sayar.

Dizin izinleri mevcut **umask** değerinden etkilenir. Web sunucunuz için umask ayarlayın, PHP’nin `umask` fonksiyonunu kullanın veya dizin oluşturulduktan sonra `chmod` fonksiyonunu çağırın.

---

### 🔍 exists

`exists()` bir veya birden fazla dosya ya da dizinin varlığını kontrol eder ve herhangi biri eksikse `false` döner:

```php
$filesystem->exists('/tmp/photos');
$filesystem->exists(['rabbit.jpg', 'bottle.png']);
```

---

### 📄 copy

`copy()` tek bir dosyayı kopyalar (`mirror()` dizinleri kopyalamak için kullanılır).

Hedef zaten mevcutsa, kaynak dosya yalnızca kaynak değişiklik tarihi hedefinkinden sonra ise kopyalanır. Bu davranış üçüncü boolean argümanla değiştirilebilir:

```php
$filesystem->copy('image-ICC.jpg', 'image.jpg');
$filesystem->copy('image-ICC.jpg', 'image.jpg', true);
```

---

### ⏰ touch

`touch()` bir dosyanın erişim ve değişiklik zamanını ayarlar. Varsayılan olarak geçerli zaman kullanılır:

```php
$filesystem->touch('file.txt');
$filesystem->touch('file.txt', time() + 10);
$filesystem->touch('file.txt', time(), time() - 10);
```

---

### 👤 chown

`chown()` bir dosyanın sahibini değiştirir. Üçüncü argüman özyinelemeli bir boolean seçenektir:

```php
$filesystem->chown('lolcat.mp4', 'www-data');
$filesystem->chown('/video', 'www-data', true);
```

---

### 👥 chgrp

`chgrp()` bir dosyanın grubunu değiştirir. Üçüncü argüman özyinelemeli bir boolean seçenektir:

```php
$filesystem->chgrp('lolcat.mp4', 'nginx');
$filesystem->chgrp('/video', 'nginx', true);
```

---

### 🔒 chmod

`chmod()` bir dosyanın modunu veya izinlerini değiştirir. Dördüncü argüman özyinelemeli boolean seçenektir:

```php
$filesystem->chmod('video.ogg', 0600);
$filesystem->chmod('src', 0700, 0000, true);
```

---

### 🗑️ remove

`remove()` dosyaları, dizinleri ve sembolik bağlantıları siler:

```php
$filesystem->remove(['symlink', '/path/to/directory', 'activity.log']);
```

---

### 📝 rename

`rename()` tek bir dosya veya dizinin adını değiştirir:

```php
$filesystem->rename('/tmp/processed_video.ogg', '/path/to/store/video_647.ogg');
$filesystem->rename('/tmp/files', '/path/to/store/files');
$filesystem->rename('/tmp/processed_video2.ogg', '/path/to/store/video_647.ogg', true);
```

---

### 🔗 symlink

`symlink()` bir hedefi belirli bir konuma sembolik bağlantı olarak oluşturur. Dosya sistemi sembolik bağlantıları desteklemiyorsa, üçüncü boolean argümanla kaynak dizin çoğaltılabilir:

```php
$filesystem->symlink('/path/to/source', '/path/to/destination');
$filesystem->symlink('/path/to/source', '/path/to/destination', true);
```

---

### 🧭 readlink

`readlink()` bağlantı hedeflerini okur.

Bu bileşenin `readlink()` metodu, tüm işletim sistemlerinde aynı şekilde davranır (PHP’nin `readlink` fonksiyonundan farklı olarak):

```php
$filesystem->readlink('/path/to/link');
$filesystem->readlink('/path/to/link', true);
```

**Davranışı:**

* `$canonicalize` `false` ise:
  * `$path` yoksa veya bağlantı değilse `null` döner.
  * `$path` bir bağlantıysa, hedefin varlığını dikkate almadan bir sonraki doğrudan hedefini döner.
* `$canonicalize` `true` ise:
  * `$path` yoksa `null` döner.
  * `$path` varsa, mutlak olarak çözülmüş son hedefi döner.

Sadece varlığı kontrol etmeden yolu kanonikleştirmek istiyorsanız, `canonicalize()` metodunu kullanabilirsiniz.


### 🧭 makePathRelative

`makePathRelative()` iki mutlak yolu alır ve ikinci yoldan birincisine göre bağıl yolu döndürür:

```php
$filesystem->makePathRelative(
    '/var/lib/symfony/src/Symfony/',
    '/var/lib/symfony/src/Symfony/Component'
);
// => '../'

$filesystem->makePathRelative('/tmp/videos', '/tmp');
// => 'videos/'
```

---

### 📦 mirror

`mirror()` kaynak dizinin tüm içeriğini hedef dizine kopyalar (`copy()` yöntemi tek dosyaları kopyalamak için kullanılır):

```php
$filesystem->mirror('/path/to/source', '/path/to/target');
```

---

### 📍 isAbsolutePath

`isAbsolutePath()` verilen yol mutlaksa `true`, değilse `false` döner:

```php
$filesystem->isAbsolutePath('/tmp'); // true
$filesystem->isAbsolutePath('c:\\Windows'); // true
$filesystem->isAbsolutePath('tmp'); // false
$filesystem->isAbsolutePath('../dir'); // false
```

---

### 🧾 tempnam

`tempnam()` benzersiz bir dosya adıyla geçici bir dosya oluşturur ve yolunu döner (başarısız olursa bir istisna fırlatır):

```php
$filesystem->tempnam('/tmp', 'prefix_');
// => /tmp/prefix_wyjgtF

$filesystem->tempnam('/tmp', 'prefix_', '.png');
// => /tmp/prefix_wyjgtF.png
```

---

### 💾 dumpFile

`dumpFile()` verilen içeriği bir dosyaya kaydeder (dosya veya dizin yoksa oluşturur).

Bu işlem atomik olarak yapılır: önce geçici bir dosyaya yazar, ardından tamamlandığında yeni konuma taşır.

Bu sayede kullanıcı hiçbir zaman kısmen yazılmış bir dosya görmez.

```php
$filesystem->dumpFile('file.txt', 'Hello World');
// file.txt artık "Hello World" içerir
```

---

### 🧩 appendToFile

`appendToFile()` bir dosyanın sonuna yeni içerik ekler:

```php
$filesystem->appendToFile('logs.txt', 'Email sent to user@example.com');
$filesystem->appendToFile('logs.txt', 'Email sent to user@example.com', true);
```

Üçüncü argüman, dosya yazılırken kilitlenip kilitlenmeyeceğini belirler.

Dosya veya içeren dizin yoksa, ekleme işleminden önce oluşturulur.

---

### 📖 readFile

🆕 Symfony 7.1 ile eklenen `readFile()` bir dosyanın tüm içeriğini string olarak döner.

PHP’nin `file_get_contents()` fonksiyonundan farklı olarak, dosya okunabilir değilse veya bir dizin yoluna geçilirse istisna fırlatır:

```php
$contents = $filesystem->readFile('/some/path/to/file.txt');
// $contents değişkeni file.txt dosyasının tüm içeriğini içerir
```

---

## 🧱 Path İşleme Yardımcıları

Dosya yollarıyla çalışmak genellikle şu zorlukları içerir:

* **Platform farklılıkları:** UNIX yolları `/` ile başlar, Windows yolları `C:` sürücüsüyle. UNIX ileri eğik çizgi (`/`), Windows ise ters eğik çizgi (`\`) kullanır.
* **Mutlak/bağıl yollar:** Web uygulamaları genellikle her ikisiyle de uğraşır.

  Dönüştürmek zor ve tekrarlayıcıdır.

`Path` sınıfı bu sorunları çözmek için yardımcı yöntemler sağlar.

---

### 🧮 Canonicalization

`canonicalize()` verilen yolun en kısa eşdeğer halini döner.

Aşağıdaki kuralları uygular:

* `.` segmentleri kaldırılır
* `..` segmentleri çözülür
* `\` karakterleri `/` ile değiştirilir
* Kök yollar (`/`, `C:/`) `/` ile biter
* Kök olmayan yollar `/` ile bitmez
* `phar://` gibi şemalar korunur
* `~` kullanıcı diziniyle değiştirilir

```php
echo Path::canonicalize('/var/www/vhost/webmozart/../config.ini');
// => /var/www/vhost/config.ini

echo Path::canonicalize('../uploads/../config/config.yaml');
// => ../config/config.yaml

echo Path::canonicalize('C:Programs/PHP/php.ini');
// => C:Programs/PHP/php.ini
```

---

### 🔗 Joining Paths

`join()` verilen yolları birleştirir ve ayraçları normalleştirir.

Dizeleri birleştirmeye daha temiz bir alternatiftir:

```php
echo Path::join('/var/www', 'vhost', 'config.ini');
// => /var/www/vhost/config.ini

echo Path::join('C:\\Program Files', 'PHP', 'php.ini');
// => C:/Program Files/PHP/php.ini
```

**Özellikleri:**

* Boş parçalar yok sayılır
* Sonraki argümanlardaki öncü eğik çizgiler kaldırılır
* Kök yollar dışındaki son eğik çizgiler kaldırılır
* İstediğiniz kadar argümanla çalışır

---

### 🔄 Absolute/Relative Paths Dönüştürme

Mutlak ve bağıl yollar `makeAbsolute()` ve `makeRelative()` yöntemleriyle dönüştürülebilir.

```php
echo Path::makeAbsolute('config/config.yaml', '/var/www/project');
// => /var/www/project/config/config.yaml

echo Path::makeAbsolute('../config/config.yaml', '/var/www/project/uploads');
// => /var/www/project/config/config.yaml

echo Path::makeRelative('/var/www/project/config/config.yaml', '/var/www/project');
// => config/config.yaml

echo Path::makeRelative('/var/www/project/config/config.yaml', '/var/www/project/uploads');
// => ../config/config.yaml
```

Bağıl veya mutlak olduğunu kontrol etmek için:

```php
Path::isAbsolute('C:\Programs\PHP\php.ini');
// => true
```

---

### 🧩 Ortak Kök Yolları Bulma

Birden fazla mutlak yol depolamak, tekrar eden bilgileri artırır:

```php
return [
    '/var/www/vhosts/project/httpdocs/config/config.yaml',
    '/var/www/vhosts/project/httpdocs/config/routing.yaml',
    '/var/www/vhosts/project/httpdocs/config/services.yaml',
    '/var/www/vhosts/project/httpdocs/images/banana.gif',
    '/var/www/vhosts/project/httpdocs/uploads/images/nicer-banana.gif',
];
```

`getLongestCommonBasePath()` ortak kök yolu bulur:

```php
$basePath = Path::getLongestCommonBasePath(
    '/var/www/vhosts/project/httpdocs/config/config.yaml',
    '/var/www/vhosts/project/httpdocs/config/routing.yaml',
    '/var/www/vhosts/project/httpdocs/config/services.yaml',
    '/var/www/vhosts/project/httpdocs/images/banana.gif',
    '/var/www/vhosts/project/httpdocs/uploads/images/nicer-banana.gif'
);
// => /var/www/vhosts/project/httpdocs
```

Ortak kök yol ile kısaltılmış yollar saklayabilirsiniz.

---

### 📁 Dizin ve Kök Dizin Bulma

PHP’nin `dirname()` fonksiyonu bazı hatalara sahiptir:

örneğin `dirname("C:/")` `"."` döner.

`getDirectory()` bunları düzeltir:

```php
echo Path::getDirectory("C:\Programs");
// => C:/
```

Kök dizini almak için `getRoot()` kullanılabilir:

```php
echo Path::getRoot("/etc/apache2/sites-available");
// => /

echo Path::getRoot("C:\Programs\Apache\Config");
// => C:/
```

---

### ⚠️ Hata Yönetimi

Bir hata oluştuğunda, `ExceptionInterface` veya `IOExceptionInterface` arayüzlerinden birini uygulayan bir istisna fırlatılır.

Örneğin, dizin oluşturma başarısız olursa bir `IOException` fırlatılır.
