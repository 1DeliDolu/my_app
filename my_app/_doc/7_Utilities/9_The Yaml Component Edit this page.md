# 🧩 Yaml Bileşeni

Symfony **Yaml** bileşeni YAML dosyalarını yükler ve yazar. YAML dizelerini PHP dizilerine ayrıştırır ve ayrıca PHP dizilerini YAML dizelerine dönüştürebilir.

 **YAML (YAML Ain't Markup Language)** , tüm programlama dilleri için insan dostu bir veri serileştirme dilidir. Okunabilirliği gelişmiş özelliklerle dengelediği için yapılandırma dosyaları için popüler bir biçimdir.

YAML spesifikasyonları hakkında daha fazla bilgi edinin.

---

## ⚙️ Kurulum

```
composer require symfony/yaml
```

Symfony uygulaması dışında bu bileşeni yüklerseniz, sınıf otomatik yükleme mekanizmasını etkinleştirmek için kodunuzda `vendor/autoload.php` dosyasını dahil etmeniz gerekir. Daha fazla ayrıntı için bu makaleyi okuyun.

---

## ❓ Neden?

### ⚡ Hızlı

Symfony Yaml'ın hedeflerinden biri hız ve özellikler arasında doğru dengeyi bulmaktır. Yapılandırma dosyalarını işlemek için gereken özellikleri destekler. Eksik olan dikkat çekici özellikler: belge yönergeleri, çok satırlı tırnaklı iletiler, sıkıştırılmış blok koleksiyonları ve çoklu belge dosyalarıdır.

### 🧠 Gerçek Bir Ayrıştırıcı

Gerçek bir ayrıştırıcıyı destekler ve tüm yapılandırma ihtiyaçlarınız için YAML spesifikasyonunun geniş bir alt kümesini ayrıştırabilir. Bu aynı zamanda ayrıştırıcının oldukça sağlam, anlaşılması kolay ve genişletilmesi basit olduğu anlamına gelir.

### 🪶 Açık Hata Mesajları

YAML dosyalarınızda bir sözdizimi hatası olduğunda, kütüphane dosya adı ve hatanın oluştuğu satır numarasıyla birlikte faydalı bir mesaj gösterir. Bu, hata ayıklamayı büyük ölçüde kolaylaştırır.

### 🧾 Dump Desteği

PHP dizilerini YAML’a dönüştürebilir ve nesne desteği ile birlikte güzel biçimlendirilmiş çıktılar için satır içi seviye yapılandırması sağlar.

### 🔢 Tür Desteği

Tarih, tamsayı, sekizlik sayılar, boolean değerleri ve daha fazlası gibi YAML yerleşik türlerinin çoğunu destekler.

### ♻️ Tam Merge Anahtar Desteği

Referanslar, takma adlar ve tam merge anahtarı için tam destek sunar. Ortak yapılandırma bölümlerine referans vererek kendinizi tekrarlamayın.

---

## 🧰 Symfony YAML Bileşenini Kullanma

Symfony Yaml bileşeni iki ana sınıftan oluşur: biri YAML dizelerini ayrıştırır ( **Parser** ), diğeri PHP dizilerini YAML dizelerine dönüştürür ( **Dumper** ).

Bu iki sınıfın üzerinde, **Yaml** sınıfı yaygın kullanımları basitleştiren ince bir sarmalayıcı olarak görev yapar.

---

## 📖 YAML İçeriğini Okuma

`parse()` metodu bir YAML dizisini ayrıştırır ve bunu bir PHP dizisine dönüştürür:

```php
use Symfony\Component\Yaml\Yaml;

$value = Yaml::parse("foo: bar");
// $value = ['foo' => 'bar']
```

Ayrıştırma sırasında bir hata oluşursa, ayrıştırıcı hata türünü ve hatanın orijinal YAML dizisinde bulunduğu satırı belirten bir **ParseException** istisnası fırlatır:

```php
use Symfony\Component\Yaml\Exception\ParseException;

try {
    $value = Yaml::parse('...');
} catch (ParseException $exception) {
    printf('Unable to parse the YAML string: %s', $exception->getMessage());
}
```

---

## 📂 YAML Dosyalarını Okuma

`parseFile()` metodu verilen dosya yolundaki YAML içeriğini ayrıştırır ve bunu bir PHP değerine dönüştürür:

```php
use Symfony\Component\Yaml\Yaml;

$value = Yaml::parseFile('/path/to/file.yaml');
```

Ayrıştırma sırasında bir hata oluşursa, ayrıştırıcı bir **ParseException** istisnası fırlatır.

---

## ✍️ YAML Dosyalarını Yazma

`dump()` metodu herhangi bir PHP dizisini YAML gösterimine dönüştürür:

```php
use Symfony\Component\Yaml\Yaml;

$array = [
    'foo' => 'bar',
    'bar' => ['foo' => 'bar', 'bar' => 'baz'],
];

$yaml = Yaml::dump($array);

file_put_contents('/path/to/file.yaml', $yaml);
```

Dönüştürme sırasında bir hata oluşursa, ayrıştırıcı bir **DumpException** istisnası fırlatır.

---

## 📚 Genişletilmiş ve Satır İçi Diziler

YAML biçimi diziler için iki tür gösterimi destekler: **genişletilmiş** ve  **satır içi** . Varsayılan olarak, **dumper** genişletilmiş gösterimi kullanır:

```yaml
foo: bar
bar:
    foo: bar
    bar: baz
```

`dump()` metodunun ikinci argümanı, çıktının hangi seviyede genişletilmiş gösterimden satır içi gösterime geçeceğini belirtir:

```php
echo Yaml::dump($array, 1);

foo: bar
bar: { foo: bar, bar: baz }

echo Yaml::dump($array, 2);

foo: bar
bar:
    foo: bar
    bar: baz
```

---

## 🔠 Girinti

Varsayılan olarak, YAML bileşeni girinti için 4 boşluk kullanır. Bu, üçüncü argümanla değiştirilebilir:

```php
// 8 boşluk kullanır
echo Yaml::dump($array, 2, 8);

foo: bar
bar:
        foo: bar
        bar: baz
```

---

## 🔢 Sayısal Değerler

Uzun sayısal değerler — ister tamsayı, ister float, ister onaltılık — kodda ve yapılandırma dosyalarında okunabilirlik açısından zayıftır. Bu nedenle YAML dosyalarında okunabilirliği artırmak için alt çizgi (`_`) eklenmesine izin verilir:

```yaml
parameters:
    credit_card_number: 1234_5678_9012_3456
    long_number: 10_000_000_000
    pi: 3.14159_26535_89793
    hex_words: 0x_CAFE_F00D
```

YAML içeriği ayrıştırılırken, tüm `_` karakterleri sayısal değerlerin içeriğinden kaldırılır. Bu nedenle ekleyebileceğiniz alt çizgi sayısı veya gruplama biçiminiz için bir sınırlama yoktur.



# ⚙️ Gelişmiş Kullanım: Bayraklar (Flags)

## 🧱 Nesne Ayrıştırma ve Yazma

`DUMP_OBJECT` bayrağını kullanarak nesneleri yazabilirsiniz:

```php
$object = new \stdClass();
$object->foo = 'bar';

$dumped = Yaml::dump($object, 2, 4, Yaml::DUMP_OBJECT);
// !php/object 'O:8:"stdClass":1:{s:5:"foo";s:7:"bar";}'
```

Ve `PARSE_OBJECT` bayrağını kullanarak ayrıştırabilirsiniz:

```php
$parsed = Yaml::parse($dumped, Yaml::PARSE_OBJECT);
var_dump(is_object($parsed)); // true
echo $parsed->foo; // bar
```

Yaml bileşeni, nesnenin dize temsili için PHP’nin `serialize()` metodunu kullanır.

⚠️ **Dikkat:** Nesne serileştirmesi bu uygulamaya özeldir. Diğer PHP YAML ayrıştırıcıları `php/object` etiketini tanımayabilir, PHP dışı uygulamalar ise kesinlikle tanımayacaktır.

---

## 📦 Nesneleri Harita (Map) Olarak Ayrıştırma ve Yazma

`DUMP_OBJECT_AS_MAP` bayrağını kullanarak nesneleri YAML haritası olarak yazabilirsiniz:

```php
$object = new \stdClass();
$object->foo = 'bar';

$dumped = Yaml::dump(['data' => $object], 2, 4, Yaml::DUMP_OBJECT_AS_MAP);
// $dumped = "data:\n    foo: bar"
```

`PARSE_OBJECT_FOR_MAP` bayrağı ile ayrıştırabilirsiniz:

```php
$parsed = Yaml::parse($dumped, Yaml::PARSE_OBJECT_FOR_MAP);
var_dump(is_object($parsed)); // true
var_dump(is_object($parsed->data)); // true
echo $parsed->data->foo; // bar
```

Yaml bileşeni, nesneleri harita olarak üretmek için PHP’nin `(array)` dönüştürmesini kullanır.

---

## 🚫 Geçersiz Türlerle Başa Çıkma

Varsayılan olarak ayrıştırıcı geçersiz türleri `null` olarak kodlar. `PARSE_EXCEPTION_ON_INVALID_TYPE` bayrağını kullanarak istisna fırlatmasını sağlayabilirsiniz:

```php
$yaml = '!php/object \'O:8:"stdClass":1:{s:5:"foo";s:7:"bar";}\'';
Yaml::parse($yaml, Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE); // istisna fırlatır
```

Benzer şekilde, yazma sırasında `DUMP_EXCEPTION_ON_INVALID_TYPE` kullanılabilir:

```php
$data = new \stdClass(); // varsayılan olarak nesneler geçersizdir.
Yaml::dump($data, 2, 4, Yaml::DUMP_EXCEPTION_ON_INVALID_TYPE); // istisna fırlatır
```

---

## 📅 Tarih İşleme

Varsayılan olarak YAML ayrıştırıcısı tarih veya tarih-zaman biçimindeki tırnaklanmamış dizeleri Unix timestamp’e dönüştürür:

```php
Yaml::parse('2016-05-27'); // 1464307200
```

`PARSE_DATETIME` bayrağını kullanarak `DateTime` nesnesine dönüştürebilirsiniz:

```php
$date = Yaml::parse('2016-05-27', Yaml::PARSE_DATETIME);
var_dump($date::class); // DateTime
```

---

## 🧾 Çok Satırlı Literal Bloklar Yazma

YAML’de çok satırlı metinler literal bloklar olarak temsil edilebilir. Varsayılan olarak dumper satırları tek satırda kodlar:

```php
$string = ["string" => "Multiple\nLine\nString"];
$yaml = Yaml::dump($string);
echo $yaml; // string: "Multiple\nLine\nString"
```

`DUMP_MULTI_LINE_LITERAL_BLOCK` bayrağını kullanarak literal blok biçiminde yazabilirsiniz:

```php
$string = ["string" => "Multiple\nLine\nString"];
$yaml = Yaml::dump($string, 2, 4, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
echo $yaml;
//  string: |
//       Multiple
//       Line
//       String
```

---

## 💡 PHP Sabitlerini Ayrıştırma

Varsayılan olarak YAML ayrıştırıcısı PHP sabitlerini normal dizeler gibi işler. `PARSE_CONSTANT` bayrağı ve `!php/const` sözdizimi ile bunları gerçek PHP sabitleri olarak ayrıştırabilirsiniz:

```php
$yaml = '{ foo: PHP_INT_SIZE, bar: !php/const PHP_INT_SIZE }';
$parameters = Yaml::parse($yaml, Yaml::PARSE_CONSTANT);
// $parameters = ['foo' => 'PHP_INT_SIZE', 'bar' => 8];
```

---

## 🧭 PHP Enum’larını Ayrıştırma

Yaml ayrıştırıcısı PHP enum türlerini (hem unit hem backed) destekler. Varsayılan olarak bunlar dizeler olarak ayrıştırılır. `PARSE_CONSTANT` bayrağı ve `!php/enum` sözdizimi ile bunları gerçek enum olarak ayrıştırabilirsiniz:

```php
enum FooEnum: string
{
    case Foo = 'foo';
    case Bar = 'bar';
}

$yaml = '{ foo: FooEnum::Foo, bar: !php/enum FooEnum::Foo }';
$parameters = Yaml::parse($yaml, Yaml::PARSE_CONSTANT);
// ['foo' => 'FooEnum::Foo', 'bar' => FooEnum::Foo]
```

Ayrıca tüm enum durumlarını almak için sadece enum sınıf adını kullanabilirsiniz:

```php
$yaml = '{ bar: !php/enum FooEnum }';
$parameters = Yaml::parse($yaml, Yaml::PARSE_CONSTANT);
// ['bar' => ['foo', 'bar']]
```

🆕 Symfony 7.1 ile enum FQCN’yi belirli bir case olmadan kullanma desteği eklendi.

---

## 🧬 İkili (Binary) Verilerin Ayrıştırılması ve Yazılması

UTF-8 olmayan dizeler base64 kodlu veri olarak yazılır:

```php
$imageContents = file_get_contents(__DIR__.'/images/logo.png');
$dumped = Yaml::dump(['logo' => $imageContents]);
// logo: !!binary iVBORw0KGgoAAAANSUhEUgAAA6oAAADqCAY...
```

`!!binary` etiketi içeren veriler otomatik olarak ayrıştırılır:

```php
$dumped = 'logo: !!binary iVBORw0KGgoAAAANSUhEUgAAA6oAAADqCAY...';
$parsed = Yaml::parse($dumped);
$imageContents = $parsed['logo'];
```

---

## 🏷️ Özel Etiketlerin Ayrıştırılması ve Yazılması

Yerleşik etiketlerin (`!php/const`, `!!binary` vb.) yanı sıra kendi özel YAML etiketlerinizi tanımlayabilir ve `PARSE_CUSTOM_TAGS` bayrağıyla ayrıştırabilirsiniz:

```php
$data = "!my_tag { foo: bar }";
$parsed = Yaml::parse($data, Yaml::PARSE_CUSTOM_TAGS);
// Symfony\Component\Yaml\Tag\TaggedValue('my_tag', ['foo' => 'bar'])
```

`TaggedValue` nesneleri YAML etiketlerine otomatik olarak dönüştürülür:

```php
use Symfony\Component\Yaml\Tag\TaggedValue;

$data = new TaggedValue('my_tag', ['foo' => 'bar']);
$dumped = Yaml::dump($data);
// !my_tag { foo: bar }
```

---

## ⚪ Null Değerleri Yazma

YAML spesifikasyonu `null` değerleri temsil etmek için hem `null` hem `~` kullanır. Varsayılan olarak bileşen `null` kullanır, `DUMP_NULL_AS_TILDE` bayrağıyla `~` kullanılabilir:

```php
$dumped = Yaml::dump(['foo' => null]);
// foo: null

$dumped = Yaml::dump(['foo' => null], 2, 4, Yaml::DUMP_NULL_AS_TILDE);
// foo: ~
```

Alternatif olarak, `DUMP_NULL_AS_EMPTY` bayrağı ile null değerleri boş dize olarak yazabilirsiniz:

```php
$dumped = Yaml::dump(['foo' => null], 2, 4, Yaml::DUMP_NULL_AS_EMPTY);
// foo:
```

🆕 Symfony 7.3 ile `DUMP_NULL_AS_EMPTY` bayrağı eklendi.

---

## 🔢 Sayısal Anahtarları Dize Olarak Yazma

Varsayılan olarak sadece sayılardan oluşan dizi anahtarları tamsayı olarak yazılır. `DUMP_NUMERIC_KEY_AS_STRING` bayrağı ile bunları dize olarak yazabilirsiniz:

```php
$dumped = Yaml::dump([200 => 'foo']);
// 200: foo

$dumped = Yaml::dump([200 => 'foo'], 2, 4, Yaml::DUMP_NUMERIC_KEY_AS_STRING);
// '200': foo
```

---

## ✨ Değerlere Çift Tırnak Ekleme

Varsayılan olarak yalnızca güvenli olmayan dizeler çift tırnak içine alınır. Tüm dizeleri çift tırnak içine almak için `DUMP_FORCE_DOUBLE_QUOTES_ON_VALUES` bayrağını kullanın:

```php
$dumped = Yaml::dump([
    'foo' => 'bar', 'some foo' => 'some bar', 'x' => 3.14, 'y' => true, 'z' => null,
], 2, 4, Yaml::DUMP_FORCE_DOUBLE_QUOTES_ON_VALUES);
```

🆕 Symfony 7.3 ile `Yaml::DUMP_FORCE_DOUBLE_QUOTES_ON_VALUES` bayrağı eklendi.

---

## 🪐 Harita Koleksiyonlarını Yazma

Varsayılan olarak YAML bileşeni harita koleksiyonlarını tire (`-`) ile ayırır:

```yaml
planets:
  -
    name: Mercury
    distance: 57910000
  -
    name: Jupiter
    distance: 778500000
```

Daha sıkıştırılmış bir çıktı için `Yaml::DUMP_COMPACT_NESTED_MAPPING` bayrağını kullanabilirsiniz:

```yaml
planets:
  - name: Mercury
    distance: 57910000
  - name: Jupiter
    distance: 778500000
```

🆕 Symfony 7.3 ile `Yaml::DUMP_COMPACT_NESTED_MAPPING` bayrağı eklendi.

---

## 🧩 Sözdizimi Doğrulama

YAML içeriğinin sözdizimi, CLI üzerinden **LintCommand** komutuyla doğrulanabilir.

Önce Console bileşenini yükleyin:

```
composer require symfony/console
```

Yalnızca `lint:yaml` komutunu içeren bir konsol uygulaması oluşturun:

```php
// lint.php
use Symfony\Component\Console\Application;
use Symfony\Component\Yaml\Command\LintCommand;

(new Application('yaml/lint'))
    ->add(new LintCommand())
    ->getApplication()
    ->setDefaultCommand('lint:yaml', true)
    ->run();
```

Daha sonra içeriği doğrulamak için komutu çalıştırın:

```bash
php lint.php path/to/file.yaml
php lint.php path/to/file1.yaml path/to/file2.yaml
php lint.php path/to/directory
cat path/to/file.yaml | php lint.php
```

JSON formatında çıktı almak için `--format=json` parametresini ekleyin:

```bash
php lint.php path/to/file.yaml --format=json
```

Lint komutu ayrıca kontrol edilen YAML dosyalarındaki kullanımdan kaldırılmış (deprecated) içerikleri de raporlar. Bu, örneğin otomatik testler sırasında YAML dosyalarının içeriğindeki uyarıları tespit etmek için yararlıdır.
