<template>
  <div class="page-container">
    
    <header class="detail-header">
      <button @click="router.back()" class="back-btn">
        ⬅ Kembali
      </button>
      <span class="header-title">Detail Produk</span>
    </header>

    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Memuat produk...</p>
    </div>

    <div v-else-if="error" class="error-state">
      <h2>😢 Produk Tidak Ditemukan</h2>
      <button @click="router.push('/')" class="btn-primary">Cari Barang Lain</button>
    </div>

    <main v-else class="content-wrapper">
      
      <div class="image-section">
        <div class="main-image-box">
          <img :src="getProductImage(product.image)" alt="Produk" class="main-img" @error="handleImageError"/>
        </div>
      </div>

      <div class="info-section">
        <div class="tags">
          <span class="tag category">{{ product.category }}</span>
          <span class="tag location">📍 {{ product.location || 'Indonesia' }}</span>
        </div>

        <h1 class="product-title">{{ product.name }}</h1>
        <h2 class="product-price">Rp {{ Number(product.price).toLocaleString('id-ID') }}</h2>

        <hr class="divider">

        <div class="description-box">
          <h3>Deskripsi Barang</h3>
          <p>{{ product.description || 'Penjual belum menambahkan deskripsi detail.' }}</p>
        </div>

        <hr class="divider">

        <div class="action-buttons">
          
          <button @click="addToCart" class="btn-cart">
            🛒 Masukkan Keranjang
          </button>
          
          <button @click="buyNow" class="btn-buy">
            💳 Beli Sekarang
          </button>

        </div>
      </div>
    </main>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../api';

const route = useRoute();
const router = useRouter();

const product = ref(null);
const loading = ref(true);
const error = ref(false);

const productId = route.params.id;

// --- FUNGSI NAVIGASI BARU ---

// 1. Masukkan ke Keranjang
const addToCart = () => {
  // Ambil data keranjang lama dari LocalStorage (jika ada)
  let cart = JSON.parse(localStorage.getItem('cart')) || [];

  // Cek apakah barang ini sudah ada di keranjang?
  const existingItem = cart.find(item => item.id === product.value.id);

  if (existingItem) {
    alert("Produk sudah ada di keranjang Anda.");
  } else {
    // Tambahkan produk ke array
    cart.push(product.value);
    // Simpan balik ke LocalStorage
    localStorage.setItem('cart', JSON.stringify(cart));
    
    // Redirect ke halaman Keranjang
    router.push('/cart');
  }
};

// 2. Beli Sekarang (Langsung ke Pembayaran)
const buyNow = () => {
  // Simpan item yang mau dibeli ke 'checkout_item' agar halaman pembayaran tahu barang apa yg dibeli
  localStorage.setItem('checkout_item', JSON.stringify([product.value]));
  
  // Redirect ke halaman Pembayaran
  router.push('/payment');
};


// --- FETCH DATA ---
const fetchDetail = async () => {
  try {
    loading.value = true;
    const res = await api.get(`/products/${productId}`);
    product.value = res.data.data || res.data;
  } catch (err) {
    console.error("Gagal ambil detail:", err);
    error.value = true;
  } finally {
    loading.value = false;
  }
};

// Helper Gambar
const getProductImage = (imgName) => {
  if (!imgName) return 'https://placehold.co/600x600?text=No+Image';
  if (imgName.startsWith('http')) return imgName;
  return `http://localhost:8000/storage/${imgName}`;
};

const handleImageError = (e) => {
  e.target.src = 'https://placehold.co/600x600?text=Error';
};

onMounted(fetchDetail);
</script>

<style scoped>
.page-container { background: #fff; min-height: 100vh; padding-bottom: 40px; }
.detail-header { position: sticky; top: 0; background: white; padding: 15px 20px; border-bottom: 1px solid #eee; display: flex; align-items: center; gap: 15px; z-index: 100; }
.back-btn { background: none; border: none; font-size: 1rem; cursor: pointer; color: #555; font-weight: bold;}
.header-title { font-weight: bold; font-size: 1.1rem; }

.content-wrapper { max-width: 1000px; margin: 0 auto; padding: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 40px; }
.main-image-box { background: #f4f4f4; border-radius: 12px; overflow: hidden; height: 400px; display: flex; align-items: center; justify-content: center; }
.main-img { width: 100%; height: 100%; object-fit: contain; }

.info-section { display: flex; flex-direction: column; }
.tags { display: flex; gap: 10px; margin-bottom: 10px; }
.tag { font-size: 0.8rem; padding: 4px 10px; border-radius: 6px; font-weight: bold; }
.category { background: #e3e8e0; color: #5e6b52; }
.location { background: #eee; color: #555; }

.product-title { margin: 0; font-size: 1.8rem; color: #333; margin-bottom: 10px; }
.product-price { margin: 0; font-size: 2rem; color: #78866B; font-weight: bold; margin-bottom: 20px; }
.divider { border: 0; border-top: 1px solid #eee; margin: 20px 0; width: 100%; }
.description-box h3 { font-size: 1.1rem; margin-bottom: 10px; }
.description-box p { line-height: 1.6; color: #666; white-space: pre-line; }

/* UPDATE STYLE TOMBOL */
.action-buttons { display: flex; gap: 15px; margin-top: auto; padding-top: 20px; }

.btn-cart { 
  flex: 1; 
  background: #fff; 
  color: #78866B; 
  border: 2px solid #78866B; /* Outline Style */
  padding: 12px; 
  border-radius: 8px; 
  font-weight: bold; 
  cursor: pointer; 
  transition: 0.2s; 
}
.btn-cart:hover { background: #f4f8f4; }

.btn-buy { 
  flex: 1; 
  background: #78866B; /* Primary Color */
  color: white; 
  border: none; 
  padding: 12px; 
  border-radius: 8px; 
  font-weight: bold; 
  cursor: pointer; 
  transition: 0.2s; 
}
.btn-buy:hover { background: #5e6b52; }

/* Loading & Error */
.loading-state, .error-state { text-align: center; padding: 50px; }
.spinner { width: 40px; height: 40px; border: 4px solid #eee; border-top: 4px solid #78866B; border-radius: 50%; margin: 0 auto 20px; animation: spin 1s linear infinite; }
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

/* Responsive */
@media (max-width: 768px) {
  .content-wrapper { grid-template-columns: 1fr; gap: 20px; }
  .main-image-box { height: 300px; }
  .product-title { font-size: 1.4rem; }
  .product-price { font-size: 1.6rem; }
  .action-buttons { position: fixed; bottom: 0; left: 0; width: 100%; background: white; padding: 15px; box-shadow: 0 -2px 10px rgba(0,0,0,0.1); z-index: 200; }
  .page-container { padding-bottom: 80px; }
}
</style>