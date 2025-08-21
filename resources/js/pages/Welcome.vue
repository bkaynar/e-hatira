<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Camera, Heart, Share2, QrCode, Users, Shield } from 'lucide-vue-next';
</script>

<template>

    <Head title="e-Hatıra - Etkinlik Anılarınızın Dijital Adresi" />

    <div class="min-h-screen animated-gradient-bg">
        <!-- Hero Section -->
        <div class="container mx-auto px-4 py-16">
            <div class="text-center text-white">
                <h1 class="hero-title mb-6">
                    e-Hatıra
                </h1>
                <p class="slogan mb-8">
                    Etkinlik Anılarınızın Dijital Adresi
                </p>
                <p class="hero-subtitle mb-12">

                </p>

                <!-- CTA Buttons -->
                <div class="flex flex-col md:flex-row gap-4 justify-center items-center mb-16">
                    <Link href="/register">
                    <Button class="cta-btn cta-primary">
                        Hemen Başlayın
                    </Button>
                    </Link>
                    <Link href="/login">
                    <Button class="cta-btn cta-secondary">
                        Giriş Yap
                    </Button>
                    </Link>
                </div>
            </div>

            <!-- Features Grid -->
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-6xl mx-auto mb-16">
                <!-- Feature 1 -->
                <Card class="feature-card">
                    <CardContent class="p-8 text-center">
                        <div class="feature-icon mb-4">
                            <Camera class="w-12 h-12 mx-auto" />
                        </div>
                        <h3 class="text-xl font-bold mb-3 text-gray-800">Kolay Fotoğraf Yükleme</h3>
                        <p class="text-gray-600">Misafirleriniz QR kod ile kolayca fotoğraf yükleyebilir. Sürükle-bırak
                            ile hızlı yükleme.</p>
                    </CardContent>
                </Card>

                <!-- Feature 2 -->
                <Card class="feature-card">
                    <CardContent class="p-8 text-center">
                        <div class="feature-icon mb-4">
                            <QrCode class="w-12 h-12 mx-auto" />
                        </div>
                        <h3 class="text-xl font-bold mb-3 text-gray-800">QR Kod ile Paylaşım</h3>
                        <p class="text-gray-600">Her etkinlik için özel QR kod. Misafirler telefonlarıyla tarattıktan
                            sonra direkt yükleme yapabilir.</p>
                    </CardContent>
                </Card>

                <!-- Feature 3 -->
                <Card class="feature-card">
                    <CardContent class="p-8 text-center">
                        <div class="feature-icon mb-4">
                            <Users class="w-12 h-12 mx-auto" />
                        </div>
                        <h3 class="text-xl font-bold mb-3 text-gray-800">Toplu Yönetim</h3>
                        <p class="text-gray-600">Tüm fotoğraflarınızı tek yerden yönetin. Onaylama, reddetme ve
                            düzenleme işlemleri bir tık ile.</p>
                    </CardContent>
                </Card>

                <!-- Feature 4 -->
                <Card class="feature-card">
                    <CardContent class="p-8 text-center">
                        <div class="feature-icon mb-4">
                            <Share2 class="w-12 h-12 mx-auto" />
                        </div>
                        <h3 class="text-xl font-bold mb-3 text-gray-800">Galeri Payla_1m1</h3>
                        <p class="text-gray-600">Etkinlik galerilerinizi public link ile payla_1n. 0steseniz indirme
                            linkini de verebilirsiniz.</p>
                    </CardContent>
                </Card>

                <!-- Feature 5 -->
                <Card class="feature-card">
                    <CardContent class="p-8 text-center">
                        <div class="feature-icon mb-4">
                            <Shield class="w-12 h-12 mx-auto" />
                        </div>
                        <h3 class="text-xl font-bold mb-3 text-gray-800">Güvenli Depolama</h3>
                        <p class="text-gray-600">Fotoğraflarınız güvenli sunucularımızda saklanır. HEIC formatları
                            otomatik JPEG'e dönüştürülür.</p>
                    </CardContent>
                </Card>

                <!-- Feature 6 -->
                <Card class="feature-card">
                    <CardContent class="p-8 text-center">
                        <div class="feature-icon mb-4">
                            <Heart class="w-12 h-12 mx-auto" />
                        </div>
                        <h3 class="text-xl font-bold mb-3 text-gray-800">Anılarınızı Koruyun</h3>
                        <p class="text-gray-600">Düğün, doğum günü, mezuniyet... Tüm özel anılarınızı dijital ortamda
                            güvenle saklayın.</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Bottom CTA -->
            <div class="text-center">
                <h2 class="text-3xl md:text-4xl font-bold text-white mb-8">
                    Hemen Başlayın, İlk Etkinliğinizi Oluşturun!
                </h2>
                <Link href="/register">
                <Button class="cta-btn cta-primary text-lg px-12 py-4">
                    Ücretsiz Hesap Oluştur
                </Button>
                </Link>
            </div>

            <!-- Footer -->
            <footer class="mt-20 text-center text-white/80">
                <div class="flex justify-center items-center gap-8 mb-8">
                    <a href="mailto:kurumsal@asyabilisim.com" class="contact-btn email-btn">
                        Destek
                    </a>
                    <a href="https://wa.me/+905400020100" class="contact-btn whatsapp-btn">
                        WhatsApp
                    </a>
                </div>
                <p class="text-sm">
                    © 2025 e-Hatira | Asya Bilişim
                </p>
            </footer>
        </div>
    </div>
</template>

<style scoped>
.animated-gradient-bg {
    background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
    background-size: 400% 400%;
    animation: gradientBG 7s ease infinite;
}

@keyframes gradientBG {
    0% {
        background-position: 0% 50%;
    }

    50% {
        background-position: 100% 50%;
    }

    100% {
        background-position: 0% 50%;
    }
}

.hero-title {
    font-family: "Poppins", sans-serif;
    font-weight: 700;
    font-size: clamp(3rem, 8vw, 6rem);
    letter-spacing: -2px;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
}

.slogan {
    font-size: clamp(1.25rem, 4vw, 2rem);
    font-weight: 300;
    opacity: 0.95;
}

.hero-subtitle {
    font-size: clamp(1rem, 3vw, 1.5rem);
    font-weight: 400;
    opacity: 0.9;
}

.cta-btn {
    font-weight: 600;
    font-size: 1.125rem;
    padding: 0.875rem 2rem;
    border-radius: 12px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    min-width: 200px;
}

.cta-primary {
    background: #fbbf24;
    color: #1f2937;
    border: none;
}

.cta-primary:hover {
    background: #f59e0b;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
}

.cta-secondary {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    border: 2px solid rgba(255, 255, 255, 0.3);
    backdrop-filter: blur(10px);
}

.cta-secondary:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
}

.feature-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: all 0.3s ease;
}

.feature-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    background: rgba(255, 255, 255, 1);
}

.feature-icon {
    color: #3b82f6;
}

.contact-btn {
    text-decoration: none;
    color: #1f2937;
    font-weight: 600;
    font-size: 1rem;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    transition: all 0.3s ease;
    display: inline-block;
}

.email-btn {
    background: #fbbf24;
}

.email-btn:hover {
    background: #f59e0b;
    transform: translateY(-2px);
}

.whatsapp-btn {
    background: #4ade80;
}

.whatsapp-btn:hover {
    background: #22c55e;
    transform: translateY(-2px);
}

@media (max-width: 768px) {
    .cta-btn {
        width: 100%;
        max-width: 300px;
    }

    .feature-card {
        margin-bottom: 1rem;
    }
}
</style>
