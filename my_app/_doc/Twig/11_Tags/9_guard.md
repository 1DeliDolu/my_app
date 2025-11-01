**guard**

🆕 **3.15**

**guard** etiketi Twig 3.15 sürümünde eklenmiştir.

**guard** ifadesi, bazı Twig çağrılabilirlerinin (callable) derleme zamanında mevcut olup olmadığını kontrol eder.

Bu sayede, aksi halde derleme hatasına yol açacak kodların derlenmesi engellenebilir.

```twig
{% guard function importmap %}
    {{ importmap('app') }}
{% endguard %}
```

İlk argüman, test edilecek Twig çağrılabilirinin türüdür ( **filter** , **function** veya  **test** ).

İkinci argüman ise test etmek istediğiniz Twig çağrılabilirinin adıdır.

Çağrılabilir mevcut değilse farklı bir kod üretebilirsiniz:

```twig
{% guard function importmap %}
    {{ importmap('app') }}
{% else %}
    {# importmap fonksiyonu mevcut değilse, yedek kod oluştur #}
{% endguard %}
```
