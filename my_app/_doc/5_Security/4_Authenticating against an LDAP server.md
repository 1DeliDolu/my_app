# LDAP sunucusuna karşı kimlik doğrulama

Bu sayfayı düzenle

Symfony, bir LDAP sunucusuyla çalışmak için farklı yollar sağlar.

Security bileşeni şunları sunar:

* `LdapUserProvider` sınıfını kullanan  **ldap user provider** . Diğer tüm user provider’lar gibi, herhangi bir authentication provider ile birlikte kullanılabilir.
* **form_login_ldap** authentication provider, bir giriş formu kullanarak LDAP sunucusuna karşı kimlik doğrulama yapmak için. Diğer tüm authentication provider’lar gibi, herhangi bir user provider ile birlikte kullanılabilir.
* **http_basic_ldap** authentication provider, HTTP Basic kullanarak LDAP sunucusuna karşı kimlik doğrulama yapmak için. Diğer tüm authentication provider’lar gibi, herhangi bir user provider ile birlikte kullanılabilir.

Bu şu senaryoların çalışabileceği anlamına gelir:

* Bir kullanıcının parolasını kontrol etme ve kullanıcı bilgilerini LDAP sunucusundan alma. Bu, LDAP user provider ve LDAP form login veya LDAP HTTP Basic authentication provider’larından biri kullanılarak yapılabilir.
* Bir kullanıcının parolasını LDAP sunucusuna karşı kontrol ederken kullanıcı bilgilerini başka bir kaynaktan (örneğin ana veritabanınızdan) alma.
* Kullanıcı bilgilerini LDAP sunucusundan yükleme, ancak başka bir kimlik doğrulama stratejisi kullanma (örneğin token tabanlı pre-authentication).

### 🧩 Kurulum

Symfony Flex kullanan uygulamalarda, Ldap bileşenini kullanmadan önce şu komutu çalıştırın:

```
composer require symfony/ldap
```

### ⚙️ Ldap Yapılandırma Referansı

Tam LDAP yapılandırma referansı için Security Configuration Reference (SecurityBundle) kısmına bakın (form_login_ldap, http_basic_ldap, ldap). Aşağıda bazı önemli seçenekler açıklanmıştır.

### 🔧 LDAP istemcisini yapılandırma

Tüm mekanizmalar önceden yapılandırılmış bir LDAP istemcisine ihtiyaç duyar. Provider’lar varsayılan olarak `ldap` adlı bir servisi kullanacak şekilde yapılandırılmıştır, ancak bu ayarı security bileşeninin yapılandırmasında geçersiz kılabilirsiniz.

Yerleşik LDAP PHP eklentisini kullanarak bir LDAP istemcisi şu şekilde yapılandırılabilir:

```php
// config/services.php
use Symfony\Component\Ldap\Adapter\ExtLdap\Adapter;
use Symfony\Component\Ldap\Ldap;

$container->register(Ldap::class)
    ->addArgument(new Reference(Adapter::class))
    ->tag('ldap');

$container
    ->register(Adapter::class)
    ->setArguments([
        'host' => 'my-server',
        'port' => 389,
        'encryption' => 'tls',
        'options' => [
            'protocol_version' => 3,
            'referrals' => false
        ],
    ]);
```

### 👥 LDAP User Provider kullanarak kullanıcıları alma

Bir LDAP sunucusundan kullanıcı bilgilerini almak istiyorsanız, **ldap user provider** kullanabilirsiniz:

```php
// config/packages/security.php
use Symfony\Component\Ldap\Ldap;
use Symfony\Config\SecurityConfig;

return static function (SecurityConfig $security): void {
    $security->provider('ldap_users')
        ->ldap()
            ->service(Ldap::class)
            ->baseDn('dc=example,dc=com')
            ->searchDn('cn=read-only-admin,dc=example,dc=com')
            ->searchPassword('password')
            ->defaultRoles(['ROLE_USER'])
            ->uidKey('uid')
            ->extraFields(['email'])
    ;
};
```

Security bileşeni, LDAP user provider kullanıldığında sağlanan giriş verilerini otomatik olarak kaçış karakterleriyle işler. Ancak LDAP bileşeni henüz böyle bir kaçış işlemi sağlamaz. Bu nedenle, bileşeni doğrudan kullanırken LDAP injection saldırılarını önlemek sizin sorumluluğunuzdadır.

Yukarıda user provider içinde yapılandırılan kullanıcı yalnızca veri almak için kullanılır. Bu kullanıcı, kullanıcı adı ve parolası tanımlanmış statik bir kullanıcıdır (güvenliği artırmak için parolayı bir ortam değişkeni olarak tanımlayın).

LDAP sunucunuz bilgilerin anonim olarak alınmasına izin veriyorsa, `search_dn` ve `search_password` seçeneklerini `null` olarak ayarlayabilirsiniz.

### 🔑 ldap user provider yapılandırma seçenekleri

**service**

type: string default: ldap

Yapılandırılmış LDAP istemcinizin adıdır. Adı özgürce seçebilirsiniz, ancak uygulamanızda benzersiz olmalı ve bir sayı ile başlayamaz veya boşluk içeremez.

**base_dn**

type: string default: null

Dizinin temel DN değeridir.

**search_dn**

type: string default: null

LDAP sunucusuna karşı kimlik doğrulama yapmak ve kullanıcı bilgilerini almak için kullanılacak read-only kullanıcınızın DN değeridir.

**search_password**

type: string default: null

LDAP sunucusuna karşı kimlik doğrulama yapmak ve kullanıcı bilgilerini almak için kullanılacak read-only kullanıcınızın parolasıdır.

**default_roles**

type: array default: []

LDAP sunucusundan alınan kullanıcıya atamak istediğiniz varsayılan roldür. Bu anahtarı yapılandırmazsanız, kullanıcılarınızın hiçbir rolü olmaz ve tam olarak kimliği doğrulanmış kabul edilmezler.

**role_fetcher**

Type: string Default: null

LDAP servisi kullanıcı rolleri sağlıyorsa, bu seçenek rolleri alan servisi tanımlamanızı sağlar. Role fetcher servisi `Symfony\Component\Ldap\Security\RoleFetcherInterface` arayüzünü uygulamalıdır. Bu seçenek ayarlandığında `default_roles` seçeneği yok sayılır.

Symfony, `Symfony\Component\Ldap\Security\MemberOfRoles` sınıfını sağlar; bu, `memberof` özniteliğinden rolleri alan somut bir uygulamadır.

> 🆕 `role_fetcher` yapılandırma seçeneği Symfony 7.3’te tanıtılmıştır.

**uid_key**

type: string default: null

Girişin UID olarak kullanılacak anahtarıdır. LDAP sunucusu uygulamanıza bağlıdır. Yaygın değerler şunlardır:

* sAMAccountName (varsayılan)
* userPrincipalName
* uid

Bu seçeneğe `null` değeri geçerseniz, varsayılan UID anahtarı `sAMAccountName` olarak kullanılır.

**extra_fields**

type: array default: null

LDAP sunucusundan alınacak özel alanları tanımlar. Herhangi bir alan mevcut değilse, bir `\InvalidArgumentException` fırlatılır.

**filter**

type: string default: null

Hangi LDAP sorgusunun kullanılacağını yapılandırmanızı sağlar. `{uid_key}` dizgesi, `uid_key` yapılandırma değerinin (varsayılan olarak `sAMAccountName`) değeriyle; `{user_identifier}` dizgesi ise yüklemeye çalıştığınız kullanıcı tanımlayıcısı ile değiştirilir.

Örneğin, `uid_key` değeri `uid` ise ve `fabpot` adlı kullanıcıyı yüklemeye çalışıyorsanız, nihai dizge şu olacaktır:

```
(uid=fabpot)
```

Bu seçeneğe `null` değeri geçerseniz, varsayılan filtre (`{uid_key}={user_identifier}`) kullanılır.

LDAP injection’ı önlemek için kullanıcı adı kaçış karakterleriyle işlenir.

`filter` anahtarının sözdizimi RFC4515 tarafından tanımlanmıştır.



# LDAP sunucusuna karşı kimlik doğrulama

LDAP sunucusuna karşı kimlik doğrulama, form login veya HTTP Basic authentication provider’ları kullanılarak yapılabilir.

Bu provider’lar, LDAP olmayan muadilleriyle tamamen aynı şekilde yapılandırılır; yalnızca iki yapılandırma anahtarı ve bir isteğe bağlı anahtar eklenir:

**service**

type: string default: ldap

Yapılandırılmış LDAP istemcinizin adıdır. Adı özgürce seçebilirsiniz, ancak uygulamanızda benzersiz olmalı ve bir sayı ile başlayamaz veya boşluk içeremez.

**dn_string**

type: string default: {user_identifier}

Bu anahtar, kullanıcı adından kullanıcının DN’ini (Distinguished Name) oluşturmak için kullanılan dizgenin biçimini tanımlar. `{user_identifier}` dizgesi, kimlik doğrulamaya çalışan kişinin gerçek kullanıcı adıyla değiştirilir.

Örneğin, kullanıcılarınızın DN dizgeleri `uid=einstein,dc=example,dc=com` biçimindeyse, `dn_string` şu şekilde olur:

```
uid={user_identifier},dc=example,dc=com
```

**query_string**

type: string default: null

Bu (isteğe bağlı) anahtar, user provider’ın bir kullanıcıyı aramasını ve ardından bulunan DN’yi bind işlemi için kullanmasını sağlar. Bu, farklı `base_dn` değerlerine sahip birden fazla LDAP user provider kullanırken faydalıdır. Bu seçeneğin değeri geçerli bir arama dizgesi olmalıdır (örneğin: `uid="{user_identifier}"`). Yer tutucu değeri, gerçek kullanıcı tanımlayıcısı ile değiştirilir.

Bu seçenek kullanıldığında, `query_string` `dn_string` içinde belirtilen DN’de arama yapar ve `query_string` sonucunda elde edilen DN, kullanıcının parolasıyla kimlik doğrulamak için kullanılır. Önceki örneğe göre, kullanıcılarınızın DN’leri `dc=companyA,dc=example,dc=com` ve `dc=companyB,dc=example,dc=com` ise, `dn_string` değeri `dc=example,dc=com` olmalıdır.

Dikkat edilmesi gereken nokta, kullanıcı adlarının her iki DN arasında benzersiz olması gerektiğidir; çünkü authentication provider birden fazla kullanıcı bulunursa bind işlemi için doğru kullanıcıyı seçemeyecektir.

Aşağıda hem `form_login_ldap` hem de `http_basic_ldap` için örnekler verilmiştir.

### 🧾 form login yapılandırma örneği

```php
// config/packages/security.php
use Symfony\Component\Ldap\Ldap;
use Symfony\Config\SecurityConfig;

return static function (SecurityConfig $security): void {
    $security->firewall('main')
        ->formLoginLdap()
            ->service(Ldap::class)
            ->dnString('uid={user_identifier},dc=example,dc=com')
    ;
};
```

### 🌐 HTTP Basic yapılandırma örneği

```php
// config/packages/security.php
use Symfony\Component\Ldap\Ldap;
use Symfony\Config\SecurityConfig;

return static function (SecurityConfig $security): void {
    $security->firewall('main')
        ->stateless(true)
        ->formLoginLdap()
            ->service(Ldap::class)
            ->dnString('uid={user_identifier},dc=example,dc=com')
    ;
};
```

### 🔍 form login ve query_string yapılandırma örneği

```php
// config/packages/security.php
use Symfony\Component\Ldap\Ldap;
use Symfony\Config\SecurityConfig;

return static function (SecurityConfig $security): void {
    $security->firewall('main')
        ->stateless(true)
        ->formLoginLdap()
            ->service(Ldap::class)
            ->dnString('dc=example,dc=com')
            ->queryString('(&(uid={user_identifier})(memberOf=cn=users,ou=Services,dc=example,dc=com))')
            ->searchDn('...')
            ->searchPassword('the-raw-password')
    ;
};
```
