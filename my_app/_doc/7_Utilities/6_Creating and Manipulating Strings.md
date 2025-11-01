
### 🧵 String’ler Oluşturma ve Manipüle Etme

Symfony, Unicode string’lerle (baytlar, kod noktaları ve grafem kümeleri olarak) çalışmak için nesne yönelimli bir API sağlar. Bu API, uygulamanıza yüklemeniz gereken **String** bileşeni aracılığıyla kullanılabilir:

```
composer require symfony/string
```

Bu bileşeni bir Symfony uygulaması dışında yüklüyorsanız, Composer tarafından sağlanan sınıf otomatik yükleme mekanizmasını etkinleştirmek için kodunuzda `vendor/autoload.php` dosyasını dahil etmelisiniz. Daha fazla bilgi için bu makaleyi okuyun.

---

### ❓ String Nedir?

Bir “kod noktası” veya “grafem kümesi” kavramlarının ne anlama geldiğini zaten biliyorsanız, bu bölümü atlayabilirsiniz. Aksi halde, bu bileşen tarafından kullanılan terminolojiyi öğrenmek için okumaya devam edin.

İngilizce gibi diller, herhangi bir içeriği görüntülemek için çok sınırlı bir karakter ve sembol kümesine ihtiyaç duyar. Her string bir dizi karakterdir (harf veya sembol) ve en basit standartlarla bile (örneğin ASCII) kodlanabilirler.

Ancak diğer diller, içeriklerini görüntülemek için binlerce sembole ihtiyaç duyar. Bu diller, Unicode gibi karmaşık kodlama standartlarına ve artık “karakter” kavramının anlamını yitirdiği yapılara ihtiyaç duyar. Bunun yerine şu terimlerle ilgilenmeniz gerekir:

* **Kod noktaları (Code points):** Bilginin atomik birimleridir. Bir string, kod noktalarının bir dizisidir. Her kod noktası, Unicode standardı tarafından anlamı belirlenen bir sayıdır. Örneğin İngilizce harf **A** için kod noktası  **U+0041** , Japonca kana **の** için  **U+306E** ’dir.
* **Grafem kümeleri (Grapheme clusters):** Bir veya daha fazla kod noktasından oluşan ve tek bir görsel birim olarak görüntülenen dizilerdir. Örneğin İspanyolca harf  **ñ** , iki kod noktasından oluşan bir grafem kümesidir:  **U+006E = n (“latin küçük harfi N”) + U+0303 = ◌̃ (“birleştirici tilde”)** .
* **Baytlar (Bytes):** String içeriği için depolanan gerçek bilgilerdir. Her kod noktası, kullanılan standarda (UTF-8, UTF-16 vb.) bağlı olarak bir veya daha fazla bayt gerektirebilir.

Aşağıdaki görsel, aynı kelimenin İngilizce (“hello”) ve Hintçe (“नमस्ते”) olarak yazılmış halindeki baytları, kod noktalarını ve grafem kümelerini göstermektedir.


![1761988738640](image/6_CreatingandManipulatingStrings/1761988738640.png)


### 🧩 Kullanım

Yeni bir  **ByteString** , **CodePointString** veya **UnicodeString** nesnesi oluşturun, string içeriğini argüman olarak geçirin ve ardından bu string’lerle çalışmak için nesne yönelimli API’yi kullanın:

```php
use Symfony\Component\String\UnicodeString;

$text = (new UnicodeString('This is a déjà-vu situation.'))
    ->trimEnd('.')
    ->replace('déjà-vu', 'jamais-vu')
    ->append('!');
// $text = 'This is a jamais-vu situation!'

$content = new UnicodeString('नमस्ते दुनिया');
if ($content->ignoreCase()->startsWith('नमस्ते')) {
    // ...
}
```

---

### 📚 Metot Referansı

#### 🏗️ String Nesneleri Oluşturma Metotları

Öncelikle, string’leri bayt, kod noktaları ve grafem kümeleri olarak saklamak için şu sınıflarla nesneler oluşturabilirsiniz:

```php
use Symfony\Component\String\ByteString;
use Symfony\Component\String\CodePointString;
use Symfony\Component\String\UnicodeString;

$foo = new ByteString('hello');
$bar = new CodePointString('hello');
// UnicodeString en yaygın kullanılan sınıftır
$baz = new UnicodeString('hello');
```

Birden fazla string nesnesi oluşturmak için `wrap()` statik metodunu kullanın:

```php
$contents = ByteString::wrap(['hello', 'world']);        // $contents = ByteString[]
$contents = UnicodeString::wrap(['I', '❤️', 'Symfony']); // $contents = UnicodeString[]

// ters dönüşüm için unwrap metodunu kullanın
$contents = UnicodeString::unwrap([
    new UnicodeString('hello'), new UnicodeString('world'),
]); // $contents = ['hello', 'world']
```

Birçok String nesnesiyle çalışıyorsanız, kodunuzu daha kısa hale getirmek için kısayol fonksiyonlarını kullanabilirsiniz:

```php
// b() fonksiyonu byte string oluşturur
use function Symfony\Component\String\b;

// her iki satır da eşdeğerdir
$foo = new ByteString('hello');
$foo = b('hello');

// u() fonksiyonu Unicode string oluşturur
use function Symfony\Component\String\u;

// her iki satır da eşdeğerdir
$foo = new UnicodeString('hello');
$foo = u('hello');

// s() fonksiyonu içeriğe göre byte veya Unicode string oluşturur
use function Symfony\Component\String\s;

// ByteString nesnesi oluşturur
$foo = s("\xfe\xff");
// UnicodeString nesnesi oluşturur
$foo = s('अनुच्छेद');
```

Bazı özel yapıcılar da mevcuttur:

```php
// ByteString verilen uzunlukta rastgele bir string oluşturabilir
$foo = ByteString::fromRandom(12);
// varsayılan olarak rastgele string base58 karakterlerini kullanır;
// ikinci opsiyonel argümanla karakter kümesini belirleyebilirsiniz
$foo = ByteString::fromRandom(6, 'AEIOU0123456789');
$foo = ByteString::fromRandom(10, 'qwertyuiop');

// CodePointString ve UnicodeString kod noktalarından string oluşturabilir
$foo = UnicodeString::fromCodePoints(0x928, 0x92E, 0x938, 0x94D, 0x924, 0x947);
// eşdeğeri: $foo = new UnicodeString('नमस्ते');
```

---

#### 🔄 String Nesnelerini Dönüştürme Metotları

Her string nesnesi, diğer iki tür nesneye dönüştürülebilir:

```php
$foo = ByteString::fromRandom(12)->toCodePointString();
$foo = (new CodePointString('hello'))->toUnicodeString();
$foo = UnicodeString::fromCodePoints(0x68, 0x65, 0x6C, 0x6C, 0x6F)->toByteString();

// opsiyonel $toEncoding argümanı hedef string’in kodlamasını tanımlar
$foo = (new CodePointString('hello'))->toByteString('Windows-1252');
// opsiyonel $fromEncoding argümanı orijinal string’in kodlamasını tanımlar
$foo = (new ByteString('さよなら'))->toCodePointString('ISO-2022-JP');
```

Eğer dönüşüm herhangi bir nedenle mümkün değilse, bir **InvalidArgumentException** alırsınız.

Belirli bir konumdaki baytları almak için de bir metot vardır:

```php
// ('नमस्ते' baytları = [224, 164, 168, 224, 164, 174, 224, 164, 184,
//                      224, 165, 141, 224, 164, 164, 224, 165, 135])
b('नमस्ते')->bytesAt(0);   // [224]
u('नमस्ते')->bytesAt(0);   // [224, 164, 168]

b('नमस्ते')->bytesAt(1);   // [164]
u('नमस्ते')->bytesAt(1);   // [224, 164, 174]
```

---

#### 📏 Uzunluk ve Boşluk Karakterleriyle İlgili Metotlar

```php
// verilen string’in grafem, kod noktası veya bayt sayısını döndürür
$word = 'नमस्ते';
(new ByteString($word))->length();      // 18 (bayt)
(new CodePointString($word))->length(); // 6 (kod noktası)
(new UnicodeString($word))->length();   // 4 (grafem)

// bazı semboller monospaced fontlarda diğerlerinden iki kat genişlik kaplar
// bu metot, kelimenin tamamını temsil etmek için gereken toplam genişliği döndürür
$word = 'नमस्ते';
(new ByteString($word))->width();      // 18
(new CodePointString($word))->width(); // 4
(new UnicodeString($word))->width();   // 4
// metin birden fazla satır içeriyorsa, tüm satırların maksimum genişliğini döndürür
$text = "<<<END
This is a
multiline text
END";
u($text)->width(); // 14

// yalnızca tam olarak boş bir string ise TRUE döndürür (boşluk bile olmamalı)
u('hello world')->isEmpty();  // false
u('     ')->isEmpty();        // false
u('')->isEmpty();             // true

// baştaki ve sondaki tüm boşlukları (' \n\r\t\x0C') kaldırır ve
// art arda gelen iki veya daha fazla boşluğu tek bir boşluk karakteriyle değiştirir
u("  \n\n   hello \t   \n\r   world \n    \n")->collapseWhitespace(); // 'hello world'
```

---

#### 🔠 Harf Biçimlerini Değiştirme Metotları

```php
// tüm grafemleri/kod noktalarını küçük harfe çevirir
u('FOO Bar Brİan')->lower();  // 'foo bar bri̇an'
// yerel dile özgü harf dönüşümüne göre küçük harfe çevirir
u('FOO Bar Brİan')->localeLower('en');  // 'foo bar bri̇an'
u('FOO Bar Brİan')->localeLower('lt');  // 'foo bar bri̇̇an'

// diller arasında küçük/büyük harf yeterli değildir; bazı karakterlerin durumu yoktur,
// bazıları bağlama veya dile bağlıdır. Bu metot, büyük/küçük harf duyarsız karşılaştırmalarda
// kullanabileceğiniz bir string döndürür
u('FOO Bar')->folded();             // 'foo bar'
u('Die O\'Brian Straße')->folded(); // "die o'brian strasse"

// tüm grafemleri/kod noktalarını büyük harfe çevirir
u('foo BAR bάz')->upper(); // 'FOO BAR BΆZ'
// yerel dile göre büyük harfe çevirir
u('foo BAR bάz')->localeUpper('en'); // 'FOO BAR BΆZ'
u('foo BAR bάz')->localeUpper('el'); // 'FOO BAR BAZ'

// baş harfleri büyük ("title case") hale getirir
u('foo ijssel')->title();               // 'Foo ijssel'
u('foo ijssel')->title(allWords: true); // 'Foo Ijssel'
// yerel dile göre baş harfleri büyük hale getirir
u('foo ijssel')->localeTitle('en'); // 'Foo ijssel'
u('foo ijssel')->localeTitle('nl'); // 'Foo IJssel'

// camelCase’e dönüştürür
u('Foo: Bar-baz.')->camel(); // 'fooBarBaz'
// snake_case’e dönüştürür
u('Foo: Bar-baz.')->snake(); // 'foo_bar_baz'
// kebab-case’e dönüştürür
u('Foo: Bar-baz.')->kebab(); // 'foo-bar-baz'
// PascalCase’e dönüştürür
u('Foo: Bar-baz.')->pascal(); // 'FooBarBaz'
// diğer biçimler metot zincirlenerek elde edilebilir
u('Foo: Bar-baz.')->camel()->upper(); // 'FOOBARBAZ'
```

> 🆕 `localeLower()`, `localeUpper()` ve `localeTitle()` metotları Symfony 7.1’de eklendi.
>
> 🆕 `kebab()` metodu Symfony 7.2’de eklendi.
>
> 🆕 `pascal()` metodu Symfony 7.3’te eklendi.

Tüm string sınıflarının metotları varsayılan olarak  **büyük/küçük harf duyarlıdır** .

Büyük/küçük harf duyarsız işlemler yapmak için `ignoreCase()` metodunu kullanabilirsiniz:

```php
u('abc')->indexOf('B');               // null
u('abc')->ignoreCase()->indexOf('B'); // 1
```

---

#### ➕ İçerik Ekleme ve Kaldırma Metotları

```php
// verilen içeriği string’in başına/sonuna ekler
u('world')->prepend('hello');      // 'helloworld'
u('world')->prepend('hello', ' '); // 'hello world'

u('hello')->append('world');      // 'helloworld'
u('hello')->append(' ', 'world'); // 'hello world'

// verilen içeriğin başta yer almasını (ya da eklenmesini) garanti eder
u('Name')->ensureStart('get');       // 'getName'
u('getName')->ensureStart('get');    // 'getName'
u('getgetName')->ensureStart('get'); // 'getName'
// benzer şekilde, içeriğin sonda olmasını sağlar
u('User')->ensureEnd('Controller');           // 'UserController'
u('UserController')->ensureEnd('Controller'); // 'UserController'
u('UserControllerController')->ensureEnd('Controller'); // 'UserController'

// verilen string’in ilk geçtiği yerden önce/sonra bulunan içeriği döndürür
u('hello world')->before('world');                  // 'hello '
u('hello world')->before('o');                      // 'hell'
u('hello world')->before('o', includeNeedle: true); // 'hello'

u('hello world')->after('hello');                  // ' world'
u('hello world')->after('o');                      // ' world'
u('hello world')->after('o', includeNeedle: true); // 'o world'

// verilen string’in son geçtiği yerden önce/sonra bulunan içeriği döndürür
u('hello world')->beforeLast('o');                      // 'hello w'
u('hello world')->beforeLast('o', includeNeedle: true); // 'hello wo'

u('hello world')->afterLast('o');                      // 'rld'
u('hello world')->afterLast('o', includeNeedle: true); // 'orld'
```


### ✂️ Doldurma (Pad) ve Kırpma (Trim) Metotları

```php
// verilen string’i belirtilen uzunluğa ulaştırmak için başına, sonuna veya her iki tarafa
// belirtilen karakter(ler)i ekler
u(' Lorem Ipsum ')->padBoth(20, '-'); // '--- Lorem Ipsum ----'
u(' Lorem Ipsum')->padStart(20, '-'); // '-------- Lorem Ipsum'
u('Lorem Ipsum ')->padEnd(20, '-');   // 'Lorem Ipsum --------'

// verilen string’i belirtilen sayıda tekrar eder
u('_.')->repeat(10); // '_._._._._._._._._._.'

// verilen karakterleri (varsayılan: boşluk karakterleri) string’in başından ve sonundan kaldırır
u('   Lorem Ipsum   ')->trim(); // 'Lorem Ipsum'
u('Lorem Ipsum   ')->trim('m'); // 'Lorem Ipsum   '
u('Lorem Ipsum')->trim('m');    // 'Lorem Ipsu'

u('   Lorem Ipsum   ')->trimStart(); // 'Lorem Ipsum   '
u('   Lorem Ipsum   ')->trimEnd();   // '   Lorem Ipsum'

// string’in başından/sonundan belirli içeriği kaldırır
u('file-image-0001.png')->trimPrefix('file-');           // 'image-0001.png'
u('file-image-0001.png')->trimPrefix('image-');          // 'file-image-0001.png'
u('file-image-0001.png')->trimPrefix('file-image-');     // '0001.png'
u('template.html.twig')->trimSuffix('.html');            // 'template.html.twig'
u('template.html.twig')->trimSuffix('.twig');            // 'template.html'
u('template.html.twig')->trimSuffix('.html.twig');       // 'template'
// birden fazla prefix/suffix verildiğinde, yalnızca ilk bulunan kaldırılır
u('file-image-0001.png')->trimPrefix(['file-', 'image-']); // 'image-0001.png'
u('template.html.twig')->trimSuffix(['.twig', '.html']);   // 'template.html'
```

---

### 🔍 Arama ve Değiştirme (Search & Replace) Metotları

```php
// string’in belirtilen içerikle başlayıp/bitip bitmediğini kontrol eder
u('https://symfony.com')->startsWith('https'); // true
u('report-1234.pdf')->endsWith('.pdf');        // true

// string içeriğinin tam olarak verilen içerikle aynı olup olmadığını kontrol eder
u('foo')->equalsTo('foo'); // true

// string içeriğinin verilen regex ile eşleşip eşleşmediğini kontrol eder
u('avatar-73647.png')->match('/avatar-(\d+)\.png/');
// sonuç = ['avatar-73647.png', '73647', null]

// preg_match() için bayraklar ikinci argüman olarak verilebilir.
// Eğer PREG_PATTERN_ORDER veya PREG_SET_ORDER verilirse, preg_match_all() kullanılır.
u('206-555-0100 and 800-555-1212')->match('/\d{3}-\d{3}-\d{4}/', \PREG_PATTERN_ORDER);
// sonuç = [['206-555-0100', '800-555-1212']]

// string’in verilen içeriklerden herhangi birini içerip içermediğini kontrol eder
u('aeiou')->containsAny('a');                 // true
u('aeiou')->containsAny(['ab', 'efg']);       // false
u('aeiou')->containsAny(['eio', 'foo', 'z']); // true

// string içinde verilen içeriğin ilk geçtiği konumu bulur
// (ikinci argüman aramanın başlayacağı konumdur; negatif değerler PHP’dekiyle aynıdır)
u('abcdeabcde')->indexOf('c');     // 2
u('abcdeabcde')->indexOf('c', 2);  // 2
u('abcdeabcde')->indexOf('c', -4); // 7
u('abcdeabcde')->indexOf('eab');   // 4
u('abcdeabcde')->indexOf('k');     // null

// string içinde verilen içeriğin son geçtiği konumu bulur
u('abcdeabcde')->indexOfLast('c');     // 7
u('abcdeabcde')->indexOfLast('c', 2);  // 7
u('abcdeabcde')->indexOfLast('c', -4); // 2
u('abcdeabcde')->indexOfLast('eab');   // 4
u('abcdeabcde')->indexOfLast('k');     // null

// tüm eşleşmeleri değiştirir
u('http://symfony.com')->replace('http://', 'https://'); // 'https://symfony.com'
// tüm regex eşleşmelerini değiştirir
u('(+1) 206-555-0100')->replaceMatches('/[^A-Za-z0-9]++/', ''); // '12065550100'
// gelişmiş değiştirme işlemleri için callable kullanılabilir
u('123')->replaceMatches('/\d/', function (string $match): string {
    return '['.$match[0].']';
}); // sonuç = '[1][2][3]'
```

---

### 🔗 Birleştirme, Bölme, Kısaltma ve Tersine Çevirme Metotları

```php
// string’i “ayraç” olarak kullanarak verilen diziyi birleştirir
u(', ')->join(['foo', 'bar']); // 'foo, bar'

// string’i verilen ayraçla parçalara ayırır
u('template_name.html.twig')->split('.');    // ['template_name', 'html', 'twig']
// ikinci argümanla maksimum parça sayısı belirtilebilir
u('template_name.html.twig')->split('.', 2); // ['template_name', 'html.twig']

// belirtilen konumdan itibaren (ve isteğe bağlı uzunlukla) alt string döndürür
u('Symfony is great')->slice(0, 7);  // 'Symfony'
u('Symfony is great')->slice(0, -6); // 'Symfony is'
u('Symfony is great')->slice(11);    // 'great'
u('Symfony is great')->slice(-5);    // 'great'

// string uzunluğu verilen değerden uzunsa kısaltır
u('Lorem Ipsum')->truncate(3);  // 'Lor'
u('Lorem Ipsum')->truncate(80); // 'Lorem Ipsum'
// ikinci argüman, kesildiğinde eklenecek karakter(ler)dir
u('Lorem Ipsum')->truncate(8, '…'); // 'Lorem I…'
// üçüncü argüman, kesme modunu belirler (varsayılan: TruncateMode::Char)
u('Lorem ipsum dolor sit amet')->truncate(8, cut: TruncateMode::Char);       // 'Lorem ip'
// uzunluğu aşmadan son tamamlanmış kelimeye kadar döndürür
u('Lorem ipsum dolor sit amet')->truncate(8, cut: TruncateMode::WordBefore); // 'Lorem'
// gerekirse uzunluğu aşarak son tamamlanmış kelimeye kadar döndürür
u('Lorem ipsum dolor sit amet')->truncate(8, cut: TruncateMode::WordAfter);  // 'Lorem ipsum'
```

> 🆕 `TruncateMode` parametresi Symfony 7.2’de tanıtıldı.

```php
// string’i belirtilen uzunlukta satırlara böler
u('Lorem Ipsum')->wordwrap(4);                  // 'Lorem\nIpsum'
// varsayılan olarak boşluklara göre böler; TRUE verilirse koşulsuz böler
u('Lorem Ipsum')->wordwrap(4, "\n", cut: true); // 'Lore\nm\nIpsu\nm'

// string’in belirli bir bölümünü verilen içerikle değiştirir
u('0123456789')->splice('xxx');       // 'xxx'
u('0123456789')->splice('xxx', 0, 2); // 'xxx23456789'
u('0123456789')->splice('xxx', 0, 6); // 'xxx6789'
u('0123456789')->splice('xxx', 6);    // '012345xxx'

// string’i belirtilen uzunlukta parçalara ayırır
u('0123456789')->chunk(3);  // ['012', '345', '678', '9']

// string içeriğini tersine çevirir
u('foo bar')->reverse();  // 'rab oof'
u('さよなら')->reverse(); // 'らなよさ'
```

---

### 💾 ByteString Tarafından Eklenen Metotlar

Bu metotlar yalnızca **ByteString** nesnelerinde kullanılabilir:

```php
// string’in UTF-8 olarak geçerli olup olmadığını kontrol eder
b('Lorem Ipsum')->isUtf8(); // true
b("\xc3\x28")->isUtf8();    // false
```

---

### 🔡 CodePointString ve UnicodeString Tarafından Eklenen Metotlar

Bu metotlar yalnızca **CodePointString** ve **UnicodeString** nesnelerinde kullanılabilir:

```php
// herhangi bir string’i ASCII kodlamasında tanımlanan Latin alfabesine dönüştürür
// (slug oluşturmak için kullanılmamalıdır; bunun için özel slugger bileşeni vardır)
u('नमस्ते')->ascii();    // 'namaste'
u('さよなら')->ascii(); // 'sayonara'
u('спасибо')->ascii();  // 'spasibo'

// belirtilen konumdaki kod noktalarını içeren bir dizi döndürür
// ('नमस्ते' grafemlerinin kod noktaları = [2344, 2350, 2360, 2340])
u('नमस्ते')->codePointsAt(0); // [2344]
u('नमस्ते')->codePointsAt(2); // [2360]
```

---

### 🔁 Unicode Normalize Etme

 **Unicode eşdeğerliği** , farklı kod noktası dizilerinin aynı karakteri temsil etmesini tanımlar.

Örneğin İsveççe harfi  **å** , tek bir kod noktası ( **U+00E5** ) veya iki kod noktası dizisi ( **U+0061 + U+030A** ) ile gösterilebilir.

`normalize()` metodu, normalizasyon modunu seçmenizi sağlar:

```php
// harfi tek bir kod noktası olarak kodlar: U+00E5
u('å')->normalize(UnicodeString::NFC);
u('å')->normalize(UnicodeString::NFKC);
// harfi iki kod noktası olarak kodlar: U+0061 + U+030A
u('å')->normalize(UnicodeString::NFD);
u('å')->normalize(UnicodeString::NFKD);
```


### 💤 Lazy-loaded String’ler

Bazı durumlarda, önceki bölümlerde gösterilen yöntemlerle bir string oluşturmak optimal değildir. Örneğin, elde edilmesi için belirli bir hesaplama gerektiren ve sonunda hiç kullanılmayabilecek bir hash değeri düşünün.

Bu tür durumlarda, değeri yalnızca ihtiyaç duyulduğunda oluşturulan bir string’i saklamaya olanak tanıyan **LazyString** sınıfını kullanmak daha iyidir:

```php
use Symfony\Component\String\LazyString;

$lazyString = LazyString::fromCallable(function (): string {
    // String değerini hesapla...
    $value = ...;

    // Son değeri döndür
    return $value;
});
```

Bu callback, program çalışması sırasında lazy string’in değeri talep edilene kadar  **gerçekleştirilmez** . Ayrıca bir **Stringable** nesnesinden de lazy string oluşturabilirsiniz:

```php
class Hash implements \Stringable
{
    public function __toString(): string
    {
        return $this->computeHash();
    }

    private function computeHash(): string
    {
        // Potansiyel olarak maliyetli hash hesaplaması yapılır
        $hash = ...;

        return $hash;
    }
}

// Bu hash’ten bir lazy string oluşturulur ve hash hesaplaması yalnızca gerektiğinde yapılır
$lazyHash = LazyString::fromStringable(new Hash());
```

---

### 😺 Emoji’lerle Çalışmak

Bu içerikler **Emoji** bileşeni belgelerine taşınmıştır.

---

### 🌀 Slugger

Bazı durumlarda (örneğin URL’ler veya dosya/dizin adları) herhangi bir Unicode karakterinin kullanılması güvenli değildir.

 **Slugger** , verilen string’i yalnızca güvenli ASCII karakterleri içeren başka bir string’e dönüştürür:

```php
use Symfony\Component\String\Slugger\AsciiSlugger;

$slugger = new AsciiSlugger();
$slug = $slugger->slug('Wôrķšƥáçè ~~sèťtïñğš~~');
// $slug = 'Workspace-settings'
```

Ek karakter dönüştürmeleri tanımlamak için bir dizi de geçebilirsiniz:

```php
$slugger = new AsciiSlugger('en', ['en' => ['%' => 'percent', '€' => 'euro']]);
$slug = $slugger->slug('10% or 5€');
// $slug = '10-percent-or-5-euro'

// yerel ayar için özel bir sembol haritası yoksa (ör. 'en_GB'),
// üst yerelin (ör. 'en') sembol haritası kullanılır
$slugger = new AsciiSlugger('en_GB', ['en' => ['%' => 'percent', '€' => 'euro']]);
$slug = $slugger->slug('10% or 5€');
// $slug = '10-percent-or-5-euro'
```

Daha dinamik dönüşümler için bir PHP closure da kullanabilirsiniz:

```php
$slugger = new AsciiSlugger('en', function (string $string, string $locale): string {
    return str_replace('❤️', 'love', $string);
});
```

Varsayılan olarak kelimeler arasındaki ayırıcı `-` (tire)’dir, ancak ikinci argümanla farklı bir ayırıcı tanımlayabilirsiniz:

```php
$slug = $slugger->slug('Wôrķšƥáçè ~~sèťtïñğš~~', '/');
// $slug = 'Workspace/settings'
```

Slugger, diğer dönüşümleri uygulamadan önce orijinal string’i Latin alfabesine **transliterasyon** yapar.

Orijinal string’in dili otomatik olarak algılanır, ancak açıkça da belirtebilirsiniz:

```php
// slugger’a Korece ('ko') dilinden transliterasyon yapılacağını belirtir
$slugger = new AsciiSlugger('ko');

// locale değerini slug() metodunun üçüncü parametresiyle geçersiz kılabilirsiniz
// örneğin bu slugger Farsça ('fa') dilinden transliterasyon yapar
$slug = $slugger->slug('...', '-', 'fa');
```

Symfony uygulamalarında slugger’ı manuel olarak oluşturmanıza gerek yoktur.

**Service autowiring** sayesinde, bir sınıfın yapıcı metodunda **SluggerInterface** ile type-hint ederek slugger’ı otomatik olarak enjekte edebilirsiniz.

Enjekte edilen slugger’ın dili, isteğin (request) diliyle aynıdır:

```php
use Symfony\Component\String\Slugger\SluggerInterface;

class MyService
{
    public function __construct(
        private SluggerInterface $slugger,
    ) {
    }

    public function someMethod(): void
    {
        $slug = $this->slugger->slug('...');
    }
}
```

---

### 🐾 Emoji Slug’ları

Emoji transliterator’ü slugger ile birleştirerek emojileri metinsel temsillerine dönüştürebilirsiniz:

```php
use Symfony\Component\String\Slugger\AsciiSlugger;

$slugger = new AsciiSlugger();
$slugger = $slugger->withEmoji();

$slug = $slugger->slug('a 😺, 🐈‍⬛, and a 🦁 go to 🏞️', '-', 'en');
// $slug = 'a-grinning-cat-black-cat-and-a-lion-go-to-national-park';

$slug = $slugger->slug('un 😺, 🐈‍⬛, et un 🦁 vont au 🏞️', '-', 'fr');
// $slug = 'un-chat-qui-sourit-chat-noir-et-un-tete-de-lion-vont-au-parc-national';
```

Belirli bir locale için emoji kullanmak veya GitHub, GitLab ya da Slack kısa kodlarını kullanmak istiyorsanız,

`withEmoji()` metodunun ilk argümanını kullanın:

```php
use Symfony\Component\String\Slugger\AsciiSlugger;

$slugger = new AsciiSlugger();
$slugger = $slugger->withEmoji('github'); // veya "en", "fr" vb.

$slug = $slugger->slug('a 😺, 🐈‍⬛, and a 🦁');
// $slug = 'a-smiley-cat-black-cat-and-a-lion';
```

---

### 🔤 Inflector

Bazı durumlarda (örneğin kod üretimi veya kod analizinde) kelimeleri **çoğuldan tekile** veya **tekilden çoğula** çevirmek gerekir.

Örneğin, bir `addStories()` metoduna karşılık gelen özelliğin `$story` olduğunu anlamak için kelimeyi çoğuldan tekile dönüştürmeniz gerekir.

Birçok dilin basit çoğul kuralları vardır, ancak aynı zamanda birçok istisna da içerir.

Örneğin İngilizcede genel kural kelimenin sonuna “s” eklemektir (book → books),

ancak bazı istisnalar vardır (woman → women, life → lives, news → news, radius → radii vb.).

Bu bileşen, İngilizce kelimeleri güvenle tekil/çoğul biçime dönüştürmek için **EnglishInflector** sınıfını sağlar:

```php
use Symfony\Component\String\Inflector\EnglishInflector;

$inflector = new EnglishInflector();

$result = $inflector->singularize('teeth');   // ['tooth']
$result = $inflector->singularize('radii');   // ['radius']
$result = $inflector->singularize('leaves');  // ['leaf', 'leave', 'leaff']

$result = $inflector->pluralize('bacterium'); // ['bacteria']
$result = $inflector->pluralize('news');      // ['news']
$result = $inflector->pluralize('person');    // ['persons', 'people']
```

Her iki metodun da döndürdüğü değer  **daima bir dizi** ’dir, çünkü bazı durumlarda verilen kelimenin tekil veya çoğul hali tek bir biçimde belirlenemeyebilir.

Symfony ayrıca diğer diller için de inflector sınıfları sağlar:

```php
use Symfony\Component\String\Inflector\FrenchInflector;

$inflector = new FrenchInflector();
$result = $inflector->singularize('souris'); // ['souris']
$result = $inflector->pluralize('hôpital');  // ['hôpitaux']

use Symfony\Component\String\Inflector\SpanishInflector;

$inflector = new SpanishInflector();
$result = $inflector->singularize('aviones'); // ['avión']
$result = $inflector->pluralize('miércoles'); // ['miércoles']
```

> 🆕 **SpanishInflector** sınıfı Symfony 7.2’de tanıtıldı.

Symfony ayrıca kendi inflector’unuzu uygulamak isterseniz bir **InflectorInterface** de sağlar.
