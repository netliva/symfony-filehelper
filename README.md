# Symfony FileHelper Bundle

Symfony projelerinde dosya işlemleri için gelişmiş yardımcı araçlar sağlayan bundle.

## Açıklama

Bu bundle, Symfony projelerinde dosya yönetimi, görüntü işleme ve dosya güvenliği için kapsamlı Twig extension'ları ve servisler sağlar. Dosya yükleme, görüntüleme, güvenli URL oluşturma ve dosya kontrolü işlemlerini kolaylaştırır.

## Özellikler

- **Dosya Yönetimi**: Dosya yükleme, silme ve organizasyon
- **Görüntü İşleme**: Thumbnail oluşturma ve görüntü optimizasyonu
- **Güvenli URL'ler**: Dosyalara güvenli erişim için URL oluşturma
- **Twig Extension'ları**: Template'lerde kolay kullanım için Twig fonksiyonları
- **Dosya Kontrolü**: Dosya varlığı ve tip kontrolü
- **Event System**: Dosya işlemleri için event-driven yapı

## Kurulum

```bash
composer require netliva/symfony-filehelper
```

### Bundle'ı Aktifleştir

```php
// config/bundles.php
return [
    // ...
    Netliva\SymfonyFileHelperBundle\NetlivaSymfonyFileHelperBundle::class => ['all' => true],
];
```

## Konfigürasyon

```yaml
# config/packages/netliva_filehelper.yaml
netliva_filehelper:
    config:
        secure_uri_prefix: "/secure/media/"
        public_uri_prefix: "/public/media/"
        upload_dir: "%kernel.project_dir%/public/uploads"
        allowed_extensions: ["jpg", "jpeg", "png", "gif", "pdf", "doc", "docx"]
        max_file_size: 10485760  # 10MB
```

## Kullanım

### Twig Fonksiyonları

#### Dosya Yolu Alma
```twig
{# Dosya yolunu al #}
{{ get_file_path('user_photos', 'profile', user.id) }}

{# Dosya varsa yolunu al #}
{{ get_file_path_if_exist('user_photos', 'profile', user.id) }}

{# Dosya varlığını kontrol et #}
{% if netliva_file_exists(file_path) %}
    <img src="{{ file_path }}" alt="Dosya">
{% endif %}
```

#### Güvenli URL'ler
```twig
{# Güvenli medya URL'i #}
<img src="{{ secure_media_uri(file_path) }}" alt="Güvenli Dosya">

{# Genel medya URL'i #}
<img src="{{ public_media_uri(file_path) }}" alt="Genel Dosya">
```

#### Dosya Görüntüleme
```twig
{# Dosyayı görüntüle #}
{{ show_file('user_photos', 'profile', user.id, {
    'class': 'img-thumbnail',
    'width': 200,
    'height': 200
}) }}

{# Dosya listesi #}
{{ get_hard_file_list('user_photos', 'profile', {
    'class': 'file-list',
    'show_thumbnails': true
}) }}
```

#### Dosya Yükleme Widget'ları
```twig
{# Dosya yükleme butonu #}
{{ file_uploader_button('user_photos', 'profile', user.id, {
    'button_text': 'Fotoğraf Yükle',
    'class': 'btn btn-primary'
}) }}

{# Tam dosya yükleme widget'ı #}
{{ file_uploader('user_photos', 'profile', {
    'multiple': true,
    'accept': 'image/*',
    'max_size': 5242880
}) }}
```

### Twig Filtreleri

```twig
{# Dosya uzantısını al #}
{{ file_path|get_extention }}

{# Yüklenen dosya sayısını al #}
{{ form.children.images|uploaded_count('images', 'user_') }}

{# Dosya thumbnail'i al #}
{{ file_path|get_file_thumbnail(150) }}

{# Boolean kontrolü #}
{{ value|is_true }}
```

### PHP Servisleri

```php
use Netliva\SymfonyFileHelperBundle\Services\NetlivaFileHelper;
use Netliva\SymfonyFileHelperBundle\Services\NetlivaImageHelper;

class FileController extends AbstractController
{
    public function upload(
        NetlivaFileHelper $fileHelper,
        NetlivaImageHelper $imageHelper
    ) {
        // Dosya yolu alma
        $filePath = $fileHelper->getFilePath('user_photos', 'profile', $userId);
        
        // Görüntü işleme
        $thumbnail = $imageHelper->createThumbnail($filePath, 200, 200);
        
        // Güvenli URL oluşturma
        $secureUrl = $fileHelper->mediaSecureUri($filePath);
        
        return $this->json([
            'file_path' => $filePath,
            'secure_url' => $secureUrl,
            'thumbnail' => $thumbnail
        ]);
    }
}
```

## Event Sistemi

### Event'ler

- `NetlivaFileHelperEvents::PUBLIC_URL`: Genel URL oluşturulurken
- `NetlivaFileHelperEvents::SECURED_URL`: Güvenli URL oluşturulurken

### Event Subscriber Örneği

```php
use Netliva\SymfonyFileHelperBundle\Event\PublicUrlEvent;
use Netliva\SymfonyFileHelperBundle\Event\NetlivaFileHelperEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FileHelperSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            NetlivaFileHelperEvents::PUBLIC_URL => 'onPublicUrl',
        ];
    }

    public function onPublicUrl(PublicUrlEvent $event): void
    {
        // URL'i özelleştir
        $path = $event->getPath();
        $customPath = '/custom/' . basename($path);
        $event->setPath($customPath);
    }
}
```

## Dosya Grupları ve Kodları

Bundle, dosyaları organize etmek için grup ve kod sistemi kullanır:

- **Grup**: Dosyaların kategorisi (örn: `user_photos`, `documents`, `products`)
- **Kod**: Grup içindeki alt kategori (örn: `profile`, `invoice`, `thumbnail`)

### Örnek Kullanım

```php
// Kullanıcı profil fotoğrafı
$fileHelper->getFilePath('user_photos', 'profile', $userId);

// Ürün görselleri
$fileHelper->getFilePath('products', 'main_image', $productId);

// Dokümanlar
$fileHelper->getFilePath('documents', 'invoice', $invoiceId);
```

## Güvenlik

- Dosya uzantısı kontrolü
- Maksimum dosya boyutu kontrolü
- Güvenli URL oluşturma
- Dosya erişim kontrolü

## Performans

- Dosya varlığı cache'leme
- Thumbnail cache'leme
- Lazy loading desteği

## Gereksinimler

- PHP >= 7.4
- Symfony >= 5.4
- Doctrine ORM >= 2.5
- Twig >= 2.0

## Lisans

MIT License 