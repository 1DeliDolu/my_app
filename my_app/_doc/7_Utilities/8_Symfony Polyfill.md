# 🧩 Symfony Polyfill

Bu proje, PHP'nin en son sürümlerinde bulunan özellikleri geriye dönük olarak getirir (backport) ve bazı eklentiler ile fonksiyonlar için uyumluluk katmanları sağlar. Farklı PHP sürümleri ve eklentiler arasında taşınabilirlik istendiğinde kullanılmak üzere tasarlanmıştır.

## 🧱 Sağlanan Polyfill’ler

Aşağıdaki bileşenler için polyfill’ler sağlanır:

* `apcu` uzantısı, eski `apc` uzantısı yüklüyken;
* PHP `ctype` uzantısı olmadan derlendiğinde `ctype` uzantısı;
* `mbstring` ve `iconv` uzantıları;
* `uuid` uzantısı;
* `MessageFormatter` sınıfı ve `msgfmt_format_message` fonksiyonları;
* `Normalizer` sınıfı ve `grapheme_*` fonksiyonları;
* `utf8_encode` ve `utf8_decode` fonksiyonları (`xml` uzantısı veya PHP 7.2 çekirdeğinden);
* `Collator`, `NumberFormatter`, `Locale` ve `IntlDateFormatter` sınıfları ("en" yerel ayarı ile sınırlıdır);
* `intl_error_name`, `intl_get_error_code`, `intl_get_error_message` ve `intl_is_failure` fonksiyonları;
* `idn_to_ascii` ve `idn_to_utf8` fonksiyonları;
* `Binary` yardımcı sınıfı (`mbstring.func_overload` ile uyumluluk gerektiğinde kullanılır);
* PHP 7.2’de tanıtılan `spl_object_id` ve `stream_isatty` fonksiyonları;
* PHP 7.2’de `mbstring` uzantısına eklenen `mb_ord`, `mb_chr` ve `mb_scrub` fonksiyonları;
* Windows’ta PHP 7.2’de tanıtılan `sapi_windows_vt100_support` fonksiyonu;
* PHP 7.2’de tanıtılan `PHP_FLOAT_*` sabitleri;
* PHP 7.2’de tanıtılan `PHP_OS_FAMILY` sabiti;
* PHP 7.3’te tanıtılan `is_countable` fonksiyonu;
* PHP 7.3’te tanıtılan `array_key_first` ve `array_key_last` fonksiyonları;
* PHP 7.3’te tanıtılan `hrtime` fonksiyonu;
* PHP 7.3’te tanıtılan `JsonException` sınıfı;
* PHP 7.4’te tanıtılan `get_mangled_object_vars`, `mb_str_split` ve `password_algos` fonksiyonları;
* PHP 8.0’da tanıtılan `fdiv` fonksiyonu;
* PHP 8.0’da tanıtılan `get_debug_type` fonksiyonu;
* PHP 8.0’da tanıtılan `preg_last_error_msg` fonksiyonu;
* PHP 8.0’da tanıtılan `str_contains` fonksiyonu;
* PHP 8.0’da tanıtılan `str_starts_with` ve `str_ends_with` fonksiyonları;
* PHP 8.0’da tanıtılan `ValueError` sınıfı;
* PHP 8.0’da tanıtılan `UnhandledMatchError` sınıfı;
* PHP 8.0’da tanıtılan `FILTER_VALIDATE_BOOL` sabiti;
* PHP 8.0’da tanıtılan `get_resource_id` fonksiyonu;
* PHP 8.0’da tanıtılan `Attribute` sınıfı;
* PHP 8.0’da tanıtılan `Stringable` arayüzü;
* Tokenizer etkin olduğunda PHP 8.0’da tanıtılan `PhpToken` sınıfı;
* PHP 8.1’de tanıtılan `array_is_list` fonksiyonu;
* PHP 8.1’de tanıtılan `enum_exists` fonksiyonu;
* PHP 8.1’de tanıtılan `MYSQLI_REFRESH_REPLICA` sabiti;
* PHP 8.1’de tanıtılan `ReturnTypeWillChange` özniteliği;
* PHP 8.1’de tanıtılan `CURLStringFile` sınıfı (yalnızca PHP >= 7.4 için);
* PHP 8.2’de tanıtılan `AllowDynamicProperties` özniteliği;
* PHP 8.2’de tanıtılan `SensitiveParameter` özniteliği;
* PHP 8.2’de tanıtılan `SensitiveParameterValue` sınıfı;
* PHP 8.2’de tanıtılan `Random\Engine` arayüzü;
* PHP 8.2’de tanıtılan `Random\CryptoSafeEngine` arayüzü;
* PHP 8.2’de tanıtılan `Random\Engine\Secure` sınıfı (diğer motorlar için `arokettu/random-polyfill`'e bakın);
* PHP 8.2’de tanıtılan `odbc_connection_string_is_quoted`, `odbc_connection_string_should_quote` ve `odbc_connection_string_quote` fonksiyonları;
* PHP 8.2’de tanıtılan `ini_parse_quantity` fonksiyonu;
* PHP 8.3’te tanıtılan `json_validate` fonksiyonu;
* PHP 8.3’te tanıtılan `Override` özniteliği;
* PHP 8.3’te tanıtılan `mb_str_pad` fonksiyonu;
* PHP 8.3’te tanıtılan `ldap_exop_sync` fonksiyonu;
* PHP 8.3’te tanıtılan `ldap_connect_wallet` fonksiyonu;
* PHP 8.3’te tanıtılan `stream_context_set_options` fonksiyonu;
* PHP 8.3’te tanıtılan `str_increment` ve `str_decrement` fonksiyonları;
* PHP 8.3’te tanıtılan `Date*Exception/Error` sınıfları;
* PHP 8.3’te tanıtılan `SQLite3Exception` sınıfı;
* PHP 8.4’te tanıtılan `mb_ucfirst` ve `mb_lcfirst` fonksiyonları;
* PHP 8.4’te tanıtılan `array_find`, `array_find_key`, `array_any` ve `array_all` fonksiyonları;
* PHP 8.4’te tanıtılan `Deprecated` özniteliği;
* PHP 8.4’te tanıtılan `mb_trim`, `mb_ltrim` ve `mb_rtrim` fonksiyonları;
* PHP 8.4’te tanıtılan `ReflectionConstant` sınıfı;
* PHP 8.4’te tanıtılan `CURL_HTTP_VERSION_3` ve `CURL_HTTP_VERSION_3ONLY` sabitleri;
* PHP 8.4’te tanıtılan `grapheme_str_split` fonksiyonu;
* PHP 8.4’te tanıtılan `bcdivmod` fonksiyonu;
* PHP 8.5’te tanıtılan `get_error_handler` ve `get_exception_handler` fonksiyonları;
* PHP 8.5’te tanıtılan `NoDiscard` özniteliği;
* PHP 8.5’te tanıtılan `array_first` ve `array_last` fonksiyonları;
* PHP 8.5’te tanıtılan `DelayedTargetValidation` özniteliği.

PHP sürümünüzü yükseltmeniz ve/veya eksik uzantıları yüklemeniz  **şiddetle tavsiye edilir** . Bu polyfill yalnızca daha iyi bir seçenek yoksa veya taşınabilirlik bir gereklilikse kullanılmalıdır.

---

## ⚙️ Uyumluluk Notları

PHP5 ve PHP7 arasında taşınabilir kod yazmak için dikkat edilmesi gerekenler:

* `\*Error` istisnaları `\Exception`'dan **önce** yakalanmalıdır.
* `error_clear_last()` çağrısından sonra `$e = error_get_last()` sonucu, `null !== $e` yerine `isset($e['message'][0])` kullanılarak doğrulanmalıdır.

---

## 📦 Kullanım

Bağımlılık yönetimi için Composer kullanıyorsanız, `symfony/polyfill` paketini değil, **bağımsız polyfill** paketlerini kullanmalısınız:

* `symfony/polyfill-apcu` → `apcu_*` fonksiyonları
* `symfony/polyfill-ctype` → `ctype` fonksiyonları
* `symfony/polyfill-php54` → PHP 5.4
* `symfony/polyfill-php55` → PHP 5.5
* `symfony/polyfill-php56` → PHP 5.6
* `symfony/polyfill-php70` → PHP 7.0
* `symfony/polyfill-php71` → PHP 7.1
* `symfony/polyfill-php72` → PHP 7.2
* `symfony/polyfill-php73` → PHP 7.3
* `symfony/polyfill-php74` → PHP 7.4
* `symfony/polyfill-php80` → PHP 8.0
* `symfony/polyfill-php81` → PHP 8.1
* `symfony/polyfill-php82` → PHP 8.2
* `symfony/polyfill-php83` → PHP 8.3
* `symfony/polyfill-php84` → PHP 8.4
* `symfony/polyfill-php85` → PHP 8.5
* `symfony/polyfill-iconv` → `iconv` fonksiyonları
* `symfony/polyfill-intl-grapheme` → `grapheme_*` fonksiyonları
* `symfony/polyfill-intl-idn` → `idn_to_ascii`, `idn_to_utf8`
* `symfony/polyfill-intl-icu` → `intl` fonksiyon ve sınıfları
* `symfony/polyfill-intl-messageformatter` → `intl messageformatter`
* `symfony/polyfill-intl-normalizer` → `intl normalizer`
* `symfony/polyfill-mbstring` → `mbstring` fonksiyonları
* `symfony/polyfill-util` → yardımcı araçlar
* `symfony/polyfill-uuid` → `uuid_*` fonksiyonları

`symfony/polyfill` paketini doğrudan istemek, Composer’ın polyfill’leri doğru şekilde paylaşmasını engeller ve gereksiz kod yüklemesine neden olur.

---

## 🧠 Tasarım

Bu paket, **düşük maliyetli** ve **yüksek kaliteli polyfill** sağlamak üzere tasarlanmıştır.

Başlangıç sürecinde yalnızca birkaç hafif `require` ifadesi ekler; uygulama sırasında ise yalnızca gerektiğinde polyfill uygulamalarını yükler.

Projede minimum PHP sürümü belirtilmişse, daha düşük sürümlere ait polyfill’leri `composer.json` içindeki `replace` bölümüne eklemek önerilir.

Bu, bu polyfill’lerin yüklenmesini engeller ve performansı artırır.

Örneğin, projeniz PHP 7.0 gerektiriyorsa ve `mb` uzantısına ihtiyaç duyuyorsa:

```json
{
    "replace": {
        "symfony/polyfill-php54": "*",
        "symfony/polyfill-php55": "*",
        "symfony/polyfill-php56": "*",
        "symfony/polyfill-php70": "*",
        "symfony/polyfill-mbstring": "*"
    }
}
```

Polyfill’ler, yerel (native) uygulamalarla birlikte birim testlerinden geçirilir, böylece uzun vadede işlevsel ve davranışsal eşitlik garanti altına alınır.

---
