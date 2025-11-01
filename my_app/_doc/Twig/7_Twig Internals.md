# 🧩 Twig’in İç Yapısı

Twig son derece genişletilebilir bir yapıya sahiptir ve üzerinde değişiklik yapabilirsiniz. Ancak, çoğu özellik ve iyileştirme bir **extension** aracılığıyla gerçekleştirilebildiğinden, çekirdek üzerinde değişiklik yapmadan önce bir **extension** oluşturmayı denemeniz tavsiye edilir. Bu bölüm, ayrıca Twig’in perde arkasında nasıl çalıştığını anlamak isteyenler için de faydalıdır.

---

## ⚙️ Twig Nasıl Çalışır?

Bir Twig şablonunun işlenmesi dört temel adımda özetlenebilir:

1. **Şablonu yükle:**

   Şablon zaten derlenmişse, yüklenir ve değerlendirme adımına geçilir; aksi halde:

   * **Lexer** önce şablonun kaynak kodunu küçük parçalara ayırır (tokenize eder);
   * Ardından  **Parser** , bu token akışını anlamlı bir düğüm ağacına (Abstract Syntax Tree - AST) dönüştürür;
   * Son olarak  **Compiler** , bu AST’yi PHP koduna dönüştürür.
2. **Şablonu değerlendir:**

   Bu, derlenmiş şablonun `display()` metodunu çağırmak ve ona context (bağlam) vermek anlamına gelir.

---

## 🧮 Lexer

 **Lexer** , bir şablonun kaynak kodunu bir  **token stream** ’e dönüştürür (her token bir `\Twig\Token` örneğidir ve akış bir `\Twig\TokenStream` örneğidir). Varsayılan lexer 15 farklı token türünü tanır:

* `\Twig\Token::BLOCK_START_TYPE`, `\Twig\Token::BLOCK_END_TYPE`: Blok sınırlayıcıları (`{% %}`)
* `\Twig\Token::VAR_START_TYPE`, `\Twig\Token::VAR_END_TYPE`: Değişken sınırlayıcıları (`{{ }}`)
* `\Twig\Token::TEXT_TYPE`: İfade dışındaki metin;
* `\Twig\Token::NAME_TYPE`: Bir ifadede ad;
* `\Twig\Token::NUMBER_TYPE`: Bir ifadede sayı;
* `\Twig\Token::STRING_TYPE`: Bir ifadede string;
* `\Twig\Token::OPERATOR_TYPE`: Bir operatör;
* `\Twig\Token::ARROW_TYPE`: Ok fonksiyonu operatörü (`=>`);
* `\Twig\Token::SPREAD_TYPE`: Yayılma operatörü (`...`);
* `\Twig\Token::PUNCTUATION_TYPE`: Noktalama işareti;
* `\Twig\Token::INTERPOLATION_START_TYPE`, `\Twig\Token::INTERPOLATION_END_TYPE`: String interpolasyonu sınırlayıcıları;
* `\Twig\Token::EOF_TYPE`: Şablon sonu.

Bir kaynak kodu manuel olarak token stream’e dönüştürmek için environment’ın `tokenize()` metodunu çağırabilirsiniz:

```php
$stream = $twig->tokenize(new \Twig\Source($source, $identifier));
```

Stream bir `__toString()` metoduna sahip olduğundan, nesneyi ekrana bastırarak metinsel bir gösterimini elde edebilirsiniz:

```php
echo $stream."\n";
```

`Hello {{ name }}` şablonunun çıktısı şöyledir:

```
TEXT_TYPE(Hello )
VAR_START_TYPE()
NAME_TYPE(name)
VAR_END_TYPE()
EOF_TYPE()
```

**Not:**

Varsayılan lexer (`\Twig\Lexer`), `setLexer()` metodu çağrılarak değiştirilebilir:

```php
$twig->setLexer($lexer);
```

---

## 🌳 Parser

 **Parser** , token stream’i bir **AST (Abstract Syntax Tree)** veya **düğüm ağacına** dönüştürür (`\Twig\Node\ModuleNode` örneği). Çekirdek extension, `for`, `if` gibi temel düğümleri ve ifade düğümlerini tanımlar.

Bir token stream’i manuel olarak node tree’ye dönüştürmek için `parse()` metodunu çağırabilirsiniz:

```php
$nodes = $twig->parse($stream);
```

Node nesnesini ekrana bastırmak, ağacın güzel bir gösterimini verir:

```
\Twig\Node\ModuleNode(
  \Twig\Node\TextNode(Hello )
  \Twig\Node\PrintNode(
    \Twig\Node\Expression\NameExpression(name)
  )
)
```

**Not:**

Varsayılan parser (`\Twig\TokenParser\AbstractTokenParser`), `setParser()` metodu ile değiştirilebilir:

```php
$twig->setParser($parser);
```

---

## 🧠 Compiler

Son adım **Compiler** tarafından gerçekleştirilir. Compiler, bir node tree’yi girdi olarak alır ve şablonun çalışma zamanında çalıştırılabilir PHP kodunu üretir.

Bir node tree’yi PHP koduna manuel olarak derlemek için `compile()` metodunu kullanabilirsiniz:

```php
$php = $twig->compile($nodes);
```

`Hello {{ name }}` şablonunun üretilmiş hali şu şekildedir (çıktı Twig sürümüne göre farklılık gösterebilir):

```php
/* Hello {{ name }} */
class __TwigTemplate_1121b6f109fe93ebe8c6e22e3712bceb extends Template
{
    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 1
        yield "Hello ";
        // line 2
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(
            (isset($context["name"]) || array_key_exists("name", $context)
                ? $context["name"]
                : (function () {
                    throw new RuntimeError('Variable "name" does not exist.', 2, $this->source);
                })()),
            "html",
            null,
            true
        );
        return; yield '';
    }

    // some more code
}
```

**Not:**

Varsayılan compiler (`\Twig\Compiler`), `setCompiler()` metodu çağrılarak değiştirilebilir:

```php
$twig->setCompiler($compiler);
```
