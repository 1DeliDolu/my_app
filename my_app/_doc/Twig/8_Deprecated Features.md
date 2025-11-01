# [Deprecated Features](https://twig.symfony.com/doc/3.x/deprecated.html#deprecated-features "Permalink to this headline")

This document lists deprecated features in Twig 3.x. Deprecated features are kept for backward compatibility and removed in the next major release (a feature that was deprecated in Twig 3.x is removed in Twig 4.0).

## [Functions](https://twig.symfony.com/doc/3.x/deprecated.html#functions "Permalink to this headline")

* The `twig_test_iterable` function is deprecated; use the native PHP `is_iterable` function instead.
* The `attribute` function is deprecated as of Twig 3.15. Use the `.` operator instead and wrap the name with parenthesis:

  ```
  {# before #}
  {{ attribute(object, method) }}
  {{ attribute(object, method, arguments) }}
  {{ attribute(array, item) }}

  {# after #}
  {{ object.(method) }}
  {{ object.(method)(arguments) }}
  {{ array[item] }}
  ```

  Note that it won't be removed in 4.0 to allow a smoother upgrade path.

## [Extensions](https://twig.symfony.com/doc/3.x/deprecated.html#extensions "Permalink to this headline")

* All functions defined in Twig extensions are marked as internal as of Twig 3.9.0, and will be removed in Twig 4.0. They have been replaced by internal methods on their respective extension classes.
  If you were using the `twig_escape_filter()` function in your code, use `$env->getRuntime(EscaperRuntime::class)->escape()` instead.
* The following methods from `Twig\Extension\EscaperExtension` are deprecated: `setEscaper()`, `getEscapers()`, `setSafeClasses`, `addSafeClasses()`. Use the same methods on the `Twig\Runtime\EscaperRuntime` class instead:
  Before: `$twig->getExtension(EscaperExtension::class)->METHOD();`
  After: `$twig->getRuntime(EscaperRuntime::class)->METHOD();`

## [Nodes](https://twig.symfony.com/doc/3.x/deprecated.html#nodes "Permalink to this headline")

* The "tag" constructor parameter of the `Twig\Node\Node` class is deprecated as of Twig 3.12 as the tag is now automatically set by the Parser when needed.
* The following `Twig\Node\Node` methods will take a string or an integer (instead of just a string) in Twig 4.0 for their "name" argument: `getNode()`, `hasNode()`, `setNode()`, `removeNode()`, and `deprecateNode()`.
* Not passing a `BodyNode` instance as the body of a `ModuleNode` or `MacroNode` constructor is deprecated as of Twig 3.12.
* Returning `null` from `TokenParserInterface::parse()` is deprecated as of Twig 3.12 (as forbidden by the interface).
* The second argument of the `Twig\Node\Expression\CallExpression::compileArguments()` method is deprecated.
* The `Twig\Node\Expression\NameExpression::isSimple()` and `Twig\Node\Expression\NameExpression::isSpecial()` methods are deprecated as
  of Twig 3.11 and will be removed in Twig 4.0.
* The `filter` node of `Twig\Node\Expression\FilterExpression` is deprecated as of Twig 3.12 and will be removed in 4.0. Use the `filter` attribute instead to get the filter:
  Before: `$node->getNode('filter')->getAttribute('value')`
  After: `$node->getAttribute('twig_callable')->getName()`
* Passing a name to `Twig\Node\Expression\FunctionExpression`, `Twig\Node\Expression\FilterExpression`, and `Twig\Node\Expression\TestExpression` is deprecated as of Twig 3.12. As of Twig 4.0, you need to pass a `TwigFunction`, `TwigFilter`, or `TestFilter` instead.
  Let's take a `FunctionExpression` as an example.
  If you have a node that extends `FunctionExpression` and if you don't override the constructor, you don't need to do anything. But if you override the constructor, then you need to change the type hint of the name and mark the constructor with the `Twig\Attribute\FirstClassTwigCallableReady` attribute.
  Before:

  ```
  class NotReadyFunctionExpression extends FunctionExpression
  {
      public function __construct(string $function, Node $arguments, int $lineno)
      {
          parent::__construct($function, $arguments, $lineno);
      }
  }

  class NotReadyFilterExpression extends FilterExpression
  {
      public function __construct(Node $node, ConstantExpression $filter, Node $arguments, int $lineno)
      {
          parent::__construct($node, $filter, $arguments, $lineno);
      }
  }

  class NotReadyTestExpression extends TestExpression
  {
      public function __construct(Node $node, string $test, ?Node $arguments, int $lineno)
      {
          parent::__construct($node, $test, $arguments, $lineno);
      }
  }
  ```

  After:

  ```
  class ReadyFunctionExpression extends FunctionExpression
  {
      #[FirstClassTwigCallableReady]
      public function __construct(TwigFunction|string $function, Node $arguments, int $lineno)
      {
          parent::__construct($function, $arguments, $lineno);
      }
  }

  class ReadyFilterExpression extends FilterExpression
  {
      #[FirstClassTwigCallableReady]
      public function __construct(Node $node, TwigFilter|ConstantExpression $filter, Node $arguments, int $lineno)
      {
          parent::__construct($node, $filter, $arguments, $lineno);
      }
  }

  class ReadyTestExpression extends TestExpression
  {
      #[FirstClassTwigCallableReady]
      public function __construct(Node $node, TwigTest|string $test, ?Node $arguments, int $lineno)
      {
          parent::__construct($node, $test, $arguments, $lineno);
      }
  }
  ```
* The following `Twig\Node\Expression\FunctionExpression` attributes are deprecated as of Twig 3.12: `needs_charset`, `needs_environment`, `needs_context`, `arguments`, `callable`, `is_variadic`, and `dynamic_name`.
* The following `Twig\Node\Expression\FilterExpression` attributes are deprecated as of Twig 3.12: `needs_charset`, `needs_environment`, `needs_context`, `arguments`, `callable`, `is_variadic`, and `dynamic_name`.
* The following `Twig\Node\Expression\TestExpression` attributes are deprecated as of Twig 3.12: `arguments`, `callable`, `is_variadic`, and `dynamic_name`.
* The `MethodCallExpression` class is deprecated as of Twig 3.15, use `MacroReferenceExpression` instead.
* The `Twig\Node\Expression\TempNameExpression` class is deprecated as of Twig 3.15; use `Twig<wbr/>\Node<wbr/>\Expression<wbr/>\Variable<wbr/>\LocalVariable` instead.
* The `Twig\Node\Expression\NameExpression` class is deprecated as of Twig 3.15; use `Twig<wbr/>\Node<wbr/>\Expression<wbr/>\Variable<wbr/>\ContextVariable` instead.
* The `Twig\Node\Expression\AssignNameExpression` class is deprecated as of Twig 3.15; use `Twig<wbr/>\Node<wbr/>\Expression<wbr/>\Variable<wbr/>\AssignContextVariable` instead.
* Node implementations that use `echo` or `print` should use `yield` instead; all Node implementations should use the `#[\Twig\Attribute\YieldReady]` attribute on their class once they've been made ready for `yield`; the `use_yield` Environment option can be turned on when all nodes use the `#[\Twig\Attribute\YieldReady]` attribute.

> * The `Twig\Node\InlinePrint` class is deprecated as of Twig 3.16 with no replacement.
> * The `Twig\Node\Expression\NullCoalesceExpression` class is deprecated as of Twig 3.17, use `Twig<wbr/>\Node<wbr/>\Expression<wbr/>\Binary<wbr/>\NullCoalesceBinary` instead.
> * The `Twig\Node\Expression\ConditionalExpression` class is deprecated as of Twig 3.17, use `Twig<wbr/>\Node<wbr/>\Expression<wbr/>\Ternary<wbr/>\ConditionalTernary` instead.
> * The `is_defined_test` attribute is deprecated as of Twig 3.21, use `Twig\Node\Expression\SupportDefinedTestInterface` instead.

* Instantiating `Twig\Node\Node` directly is deprecated as of Twig 3.15. Use `EmptyNode` or `Nodes` instead depending on the use case. The `Twig\Node\Node` class will be abstract in Twig 4.0.
* Not passing `AbstractExpression` arguments to the following `Node` class constructors is deprecated as of Twig 3.15:
  * `AbstractBinary`
  * `AbstractUnary`
  * `BlockReferenceExpression`
  * `TestExpression`
  * `DefinedTest`
  * `FilterExpression`
  * `RawFilter`
  * `DefaultFilter`
  * `InlinePrint`
  * `NullCoalesceExpression`

# ⚠️ Kullanımdan Kaldırılmış Özellikler (Devam)

Bu bölüm Twig 3.x’te kullanımdan kaldırılmış ek özellikleri listeler. Bu özellikler Twig 4.0’da tamamen kaldırılacaktır.

---

## 🌿 Node Visitors

* `Twig\NodeVisitor\AbstractNodeVisitor` sınıfı kullanımdan kaldırılmıştır. Bunun yerine **`Twig\NodeVisitor\NodeVisitorInterface`** arayüzünü uygulayın.
* `Twig\NodeVisitor\OptimizerNodeVisitor::OPTIMIZE_RAW_FILTER` ve `Twig\NodeVisitor\OptimizerNodeVisitor::OPTIMIZE_TEXT_NODES` seçenekleri Twig 3.12 itibarıyla kullanımdan kaldırılmıştır ve Twig 4.0’da kaldırılacaktır; artık hiçbir işlevleri yoktur.

---

## 🧩 Parser

* Aşağıdaki `Twig\Parser` metotları Twig 3.12 itibarıyla kullanımdan kaldırılmıştır:

  `getBlockStack()`, `hasBlock()`, `getBlock()`, `hasMacro()`, `hasTraits()`, `getParent()`.
* `Twig\Parser::setParent()` metoduna `null` geçirmek Twig 3.12 itibarıyla kullanımdan kaldırılmıştır.
* `Twig\Parser::getExpressionParser()` Twig 3.21 itibarıyla kullanımdan kaldırılmıştır; bunun yerine **`Twig\Parser::parseExpression()`** kullanın.
* `Twig\ExpressionParser` sınıfı Twig 3.21 itibarıyla tamamen kullanımdan kaldırılmıştır. Aşağıdaki metotlar yerine belirtilen alternatifleri kullanın:

| Eski Metot                      | Yeni Kullanım                                                        |
| ------------------------------- | --------------------------------------------------------------------- |
| `parseExpression()`           | `Parser::parseExpression()`                                         |
| `parsePrimaryExpression()`    | `Parser::parseExpression()`                                         |
| `parseStringExpression()`     | `Parser::parseExpression()`                                         |
| `parseHashExpression()`       | `Parser::parseExpression()`                                         |
| `parseMappingExpression()`    | `Parser::parseExpression()`                                         |
| `parseArrayExpression()`      | `Parser::parseExpression()`                                         |
| `parseSequenceExpression()`   | `Parser::parseExpression()`                                         |
| `parseArguments()`            | `Twig\ExpressionParser\Infix\ArgumentsTrait::parseNamedArguments()` |
| `parseAssignmentExpression()` | `AbstractTokenParser::parseAssignmentExpression()`                  |
| `parseOnlyArguments()`        | `Twig\ExpressionParser\Infix\ArgumentsTrait::parseNamedArguments()` |

Diğer metotlar (`parsePostfixExpression`, `parseSubscriptExpression`, `parseFilterExpression`, `parseFilterExpressionRaw`, `parseMultitargetExpression`) artık kaldırılmıştır.

---

## 🧱 Token

* `Twig\TokenStream` yapıcısına bir **Source** örneği geçmemek Twig 3.16 itibarıyla kullanımdan kaldırılmıştır.
* `Token::getType()` Twig 3.19 itibarıyla kullanımdan kaldırılmıştır; bunun yerine **`Token::test()`** kullanın.
* `Token::ARROW_TYPE` sabiti Twig 3.21 itibarıyla kullanımdan kaldırılmıştır; `=>` artık bir **operator** (`Token::OPERATOR_TYPE`) olarak kabul edilir.
* `Token::PUNCTUATION_TYPE` sabitiyle `(`, `[`, `|`, `.`, `?`, `?:` karakterleri artık **`Token::OPERATOR_TYPE`** olarak sınıflandırılır.

---

## 🧩 Templates

* `Template::loadTemplate()` metodu kullanımdan kaldırılmıştır.
* Twig genel API’lerine (`Environment::resolveTemplate()`, `Environment::load()` vb.) **`Twig\Template`** örneği geçirmek kullanımdan kaldırılmıştır; bunun yerine **`Twig\TemplateWrapper`** örneği geçirin.

---

## 🧹 Filtreler

* `spaceless` filtresi Twig 3.12 itibarıyla kullanımdan kaldırılmıştır ve Twig 4.0’da kaldırılacaktır.

---

## 🧱 Sandbox

* **Sandbox** içinde `extends` ve `use` tag’larının varsayılan olarak izinli olması Twig 3.12 itibarıyla kullanımdan kaldırılmıştır. Twig 4.0’da bunlara ihtiyaç duyarsanız açıkça izin vermeniz gerekir.
* **`sandbox` tag’i** kullanımdan kaldırılmıştır. Bunun yerine `include` fonksiyonunun **`sandboxed`** seçeneğini kullanın:

```twig
{# Önce #}
{% sandbox %}
  {% include 'user_defined.html.twig' %}
{% endsandbox %}

{# Sonra #}
{{ include('user_defined.html.twig', sandboxed: true) }}
```

---

## 🧪 Test Yardımcıları

* `Twig\Test\NodeTestCase::getTests()` metodunu uygulamak Twig 3.13 itibarıyla kullanımdan kaldırılmıştır. Bunun yerine **statik** `provideTests()` metodunu uygulayın.
* `getVariableGetter()` ve `getAttributeGetter()` metotları kullanımdan kaldırılmıştır. Bunların yerine **`createVariableGetter()`** ve **`createAttributeGetter()`** metotlarını çağırın.
* `Twig\Test\NodeTestCase::getEnvironment()` metodu Twig 3.13 itibarıyla **final** olarak kabul edilmiştir. Twig ortamını özelleştirmek istiyorsanız **`createEnvironment()`** metodunu override edin.
* `Twig\Test\IntegrationTestCase::getFixturesDir()` kullanımdan kaldırılmıştır; bunun yerine **statik** `getFixturesDirectory()` metodunu uygulayın (Twig 4.0’da abstract olacaktır).
* `getTests()` ve `getLegacyTests()` veri sağlayıcıları Twig 3.13 itibarıyla **final** olarak işaretlenmiştir.

---

## 🌍 Environment

* `Twig\Environment::mergeGlobals()` metodu Twig 3.14 itibarıyla kullanımdan kaldırılmıştır ve Twig 4.0’da kaldırılacaktır.

  Bunun yerine aşağıdaki gibi kullanın:

```php
// Önce:
$context = $twig->mergeGlobals($context);

// Sonra:
$context += $twig->getGlobals();
```

---

## 🔧 Fonksiyonlar / Filtreler / Testler

* `deprecated`, `deprecating_package`, `alternative` seçenekleri Twig 3.15 itibarıyla kullanımdan kaldırılmıştır. Bunların yerine **`deprecation_info`** seçeneğini kullanın:

```php
// Önce:
$twig->addFunction(new TwigFunction('upper', 'upper', [
    'deprecated' => '3.12', 'deprecating_package' => 'twig/twig',
]));

// Sonra:
$twig->addFunction(new TwigFunction('upper', 'upper', [
    'deprecation_info' => new DeprecatedCallableInfo('twig/twig', '3.12'),
]));
```

* Değişken sayıda argümanlar (variadic arguments) için, Twig 4.0 geçişini kolaylaştırmak adına **snake_case** biçiminde argüman adları kullanın.
* Arrow function kabul eden Twig callable argümanlarına **string** veya **array** geçirmek Twig 3.15 itibarıyla kullanımdan kaldırılmıştır; Twig 4.0’da bu argümanlar **`\Closure`** tip ipucuna sahip olacaktır.
* `TwigFilter::getSafe()` ve `TwigFunction::getSafe()` metotlarından `null` döndürmek Twig 3.16 itibarıyla kullanımdan kaldırılmıştır; bunun yerine **boş dizi `[]`** döndürün.

---

## ➕ Operatörler

* Bir operatör önceliği Twig 3.21 itibarıyla **[0, 512]** aralığında olmalıdır.
* `.` operatörü Twig 3.15 itibarıyla sınıf sabitlerine erişime izin verir. (Eğer sabit isimleri **BÜYÜK HARFLE** yazılmıyorsa, bu geriye dönük uyumluluk problemi oluşturabilir.)
* `~` operatörünü `+` veya `-` ile birlikte parantez olmadan kullanmak Twig 3.15 itibarıyla kullanımdan kaldırılmıştır. Twig 4.0’da `+` ve `-` operatörleri `~`’den daha yüksek önceliğe sahip olacaktır.

**Örnek:**

```twig
{{ '42' ~ 1 + 41 }} {# kullanımdan kaldırılmış #}

{{ ('42' ~ 1) + 41 }} {# Twig 3.x davranışı #}
{{ '42' ~ (1 + 41) }} {# Twig 4.x davranışı #}
```

* `??` operatörünü parantezsiz kullanmak Twig 3.15 itibarıyla kullanımdan kaldırılmıştır. Twig 4.0’da `??` en düşük önceliğe sahip olacaktır.

```twig
{{ 'notnull' ?? 'foo' ~ '_bar' }} {# kullanımdan kaldırılmış #}

{{ ('notnull' ?? 'foo') ~ '_bar' }} {# Twig 3.x #}
{{ 'notnull' ?? ('foo' ~ '_bar') }} {# Twig 4.x #}
```

* `not` unary operatörünü `*`, `/`, `//`, `%` operatörleriyle birlikte parantezsiz kullanmak Twig 3.15 itibarıyla kullanımdan kaldırılmıştır.

```twig
{{ not 1 * 2 }} {# kullanımdan kaldırılmış #}

{{ (not 1 * 2) }} {# Twig 3.x #}
{{ (not 1) * 2 }} {# Twig 4.x #}
```

* `|` operatörünü `+` veya `-` ile birlikte parantezsiz kullanmak Twig 3.21 itibarıyla kullanımdan kaldırılmıştır. Twig 4.0’da `|`, `+` ve `-`’den daha yüksek önceliğe sahip olacaktır.

```twig
{{ -1|abs }} {# kullanımdan kaldırılmış #}

{{ -(1|abs) }} {# Twig 3.x #}
{{ (-1)|abs }} {# Twig 4.x #}
```

---

## 🧠 Operatör Tanımlamaları

* `Twig\Extension\ExtensionInterface::getOperators()` Twig 3.21 itibarıyla kullanımdan kaldırılmıştır.

  Bunun yerine **`getExpressionParsers()`** kullanın:

```php
// Önce:
public function getOperators(): array {
    return [
        'not' => [
            'precedence' => 10,
            'class' => NotUnary::class,
        ],
    ];
}

// Sonra:
public function getExpressionParsers(): array {
    return [
        new UnaryOperatorExpressionParser(NotUnary::class, 'not', 10),
    ];
}
```

* `Twig\OperatorPrecedenceChange` sınıfı Twig 3.21 itibarıyla kullanımdan kaldırılmıştır; yerine **`Twig\ExpressionParser\PrecedenceChange`** kullanın.
