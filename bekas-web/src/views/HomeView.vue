<template>
  <div class="page-container">
    
    <header class="main-header">
      <div class="header-content">
        <div class="brand-logo" @click="router.push('/')">
          <img src="/logo_bekas_asli.png" alt="Logo" class="logo-img" />
          <!-- <span>Bekas</span> -->
        </div>

        <div class="search-container">
          <span class="search-icon">🔍</span>
          <input 
            type="text" 
            placeholder="Cari barang bekas murah..." 
            v-model="searchQuery" 
          />
        </div>

        <div class="desktop-actions">
          <button class="d-btn" :class="{ active: $route.path === '/' }" @click="router.push('/')">Beranda</button>
          <button class="d-btn primary" @click="router.push('/jual')">+ Jual Barang</button>
          
          <button class="d-btn icon-btn-desktop" @click="router.push('/cart')" title="Keranjang">
            🛒
          </button>

          <button class="d-btn" @click="router.push('/profile')">Akun Saya</button>
        </div>

        <div class="mobile-actions">
          <button class="icon-btn">🔔</button>
          <button class="icon-btn" @click="router.push('/cart')">🛒</button>
        </div>
      </div>
    </header>

    <main class="content-body">
      
      <section class="promo-banner">
        <div class="banner-info">
          <h2>Promo Gajian!</h2>
          <p>Diskon s/d 70% untuk Barang Elektronik & Hobi</p>
          <button class="banner-btn">Cek Sekarang</button>
        </div>
        <div class="banner-decor"></div>
      </section>

      <section class="section">
        <h3 class="section-title">Kategori Pilihan</h3>
        <div class="category-list">
          <div 
            class="cat-item" 
            v-for="(cat, i) in categories" 
            :key="i"
            @click="selectedCategory = cat.name"
          >
            <div class="cat-icon-box" :class="{ 'active': selectedCategory === cat.name }">
              {{ cat.icon }}
            </div>
            <span class="cat-label">{{ cat.name }}</span>
          </div>
        </div>
      </section>

      <section class="section">
        <div class="section-header-flex">
          <h3 class="section-title">
            {{ selectedCategory === 'Semua' ? 'Rekomendasi Untukmu' : 'Kategori: ' + selectedCategory }}
          </h3>
          <span v-if="searchQuery" class="search-result-tag">Hasil pencarian: "{{ searchQuery }}"</span>
        </div>
        
        <div v-if="filteredProducts.length > 0" class="product-grid">
          <div 
            class="product-card" 
            v-for="item in filteredProducts" 
            :key="item.id"
            @click="goToDetail(item.id)" 
          >
            <div class="product-img">
              <img 
                :src="getProductImage(item.image)" 
                @error="handleImageError"
                alt="Produk"
              />
              <div class="badge-loc">{{ item.location || 'Indonesia' }}</div>
            </div>
            
            <div class="product-detail">
              <h4 class="p-name">{{ item.name }}</h4>
              <p class="p-category">{{ item.category }}</p>
              <p class="p-price">Rp {{ Number(item.price).toLocaleString('id-ID') }}</p>
            </div>
          </div>
        </div>

        <div v-else class="empty-state">
          <div style="font-size: 50px;">🔍</div>
          <p>Barang tidak ditemukan.</p>
          <button v-if="selectedCategory !== 'Semua' || searchQuery !== ''" @click="resetFilter" class="btn-reset">Lihat Semua Barang</button>
        </div>
      </section>
    </main>

    <nav class="bottom-nav">
      <div class="nav-link" :class="{ active: selectedCategory === 'Semua' && !searchQuery }" @click="resetFilter"> 
        <span class="nav-icon">🏠</span>
        <span>Beranda</span>
      </div>
      <div class="nav-link-center" @click="router.push('/jual')">
        <div class="plus-btn">+</div>
        <span>Jual</span>
      </div>
      <div class="nav-link" @click="router.push('/profile')">
        <span class="nav-icon">👤</span>
        <span>Saya</span>
      </div>
    </nav>

  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import api from '../api';

const router = useRouter();
const products = ref([]);
const searchQuery = ref('');
const selectedCategory = ref('Semua'); // State kategori aktif

const categories = [
  { name: 'Semua', icon: '👁️' },
  { name: 'Gadget', icon: '📱' },
  { name: 'Fashion', icon: '👕' },
  { name: 'Otomotif', icon: '🚗' },
  { name: 'Hobi', icon: '🎸' }
];

// --- LOGIKA FILTERING (CORE) ---
const filteredProducts = computed(() => {
  return products.value.filter(product => {
    // Filter Kategori
    const matchCategory = selectedCategory.value === 'Semua' || product.category === selectedCategory.value;
    
    // Filter Search (berdasarkan nama atau kategori)
    const matchSearch = product.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                        product.category.toLowerCase().includes(searchQuery.value.toLowerCase());
    
    return matchCategory && matchSearch;
  });
});

const resetFilter = () => {
  selectedCategory.value = 'Semua';
  searchQuery.value = '';
  router.push('/');
};

// --- FUNGSI NAVIGASI ---
const goToDetail = (id) => {
  router.push(`/product/${id}`);
};

// --- HANDLING GAMBAR ---
const getProductImage = (imgName) => {
  if (!imgName) return 'https://placehold.co/300x300?text=No+Image';
  if (imgName.startsWith('http')) return imgName;
  return `http://localhost:8000/storage/${imgName}`;
};

const handleImageError = (e) => {
  e.target.src = 'https://placehold.co/300x300?text=Error+Loading';
};

// --- FETCH DATA ---
const fetchProducts = async () => {
  try {
    const res = await api.get('/products');
    // Sesuaikan dengan struktur respons Laravel kamu
    products.value = res.data.data || res.data;
  } catch (err) {
    console.error("Gagal load data:", err);
  }
};

onMounted(fetchProducts);
</script>

<style scoped>
/* Gunakan semua style CSS yang sudah kamu punya, 
   tambahkan sedikit perbaikan untuk tombol reset & filter di bawah ini */

.search-result-tag { font-size: 0.8rem; color: #78866B; font-style: italic; }
.section-header-flex { display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px; }

.btn-reset {
  background: #78866B;
  color: white;
  border: none;
  padding: 8px 15px;
  border-radius: 20px;
  margin-top: 10px;
  cursor: pointer;
}

/* Memastikan kategori list tidak terpotong */
.category-list {
  display: flex;
  gap: 15px;
  overflow-x: auto;
  padding: 5px 5px 15px 5px;
  scrollbar-width: none; /* Hide scrollbar Firefox */
}
.category-list::-webkit-scrollbar { display: none; } /* Hide scrollbar Chrome */

.cat-icon-box.active {
  background: #78866B !important;
  color: white !important;
  border-color: #78866B !important;
}

/* Copy semua CSS lama kamu di sini... */
/* (Saya ringkas untuk efisiensi pesan, tapi gunakan CSS dari codingan aslimu) */
.page-container { background: #f8f9fa; min-height: 100vh; display: flex; flex-direction: column; }
.main-header { background: #fff; border-bottom: 1px solid #eee; position: sticky; top: 0; z-index: 100; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
.header-content { max-width: 1200px; margin: 0 auto; padding: 15px 20px; display: flex; align-items: center; justify-content: space-between; gap: 20px; }
.brand-logo { font-weight: bold; font-size: 1.2rem; color: #78866B; display: flex; align-items: center; gap: 10px; cursor: pointer; }
.logo-img { height: 75px; width: auto; }
.search-container { flex: 1; background: #f1f1f1; border-radius: 8px; display: flex; align-items: center; padding: 10px 15px; max-width: 500px; }
.search-container input { border: none; background: transparent; width: 100%; outline: none; margin-left: 10px; font-size: 14px; }
.desktop-actions { display: none; gap: 15px; align-items: center; }
.d-btn { background: none; border: none; cursor: pointer; font-weight: 600; color: #555; font-size: 14px; }
.d-btn.active { color: #78866B; }
.d-btn.primary { background: #78866B; color: white; padding: 8px 20px; border-radius: 6px; }
.mobile-actions { display: flex; gap: 15px; }
.icon-btn { background: none; border: none; font-size: 20px; cursor: pointer; }
.content-body { max-width: 1200px; width: 100%; margin: 0 auto; padding: 20px; padding-bottom: 80px; }
.promo-banner { background: linear-gradient(135deg, #78866B, #94a386); border-radius: 12px; padding: 30px; color: white; margin-bottom: 30px; display: flex; align-items: center; justify-content: space-between; }
.banner-btn { background: white; border: none; padding: 10px 20px; border-radius: 25px; color: #78866B; font-weight: bold; cursor: pointer; }
.section { margin-bottom: 30px; }
.section-title { font-size: 1.2rem; font-weight: bold; color: #333; }
.cat-item { display: flex; flex-direction: column; align-items: center; min-width: 70px; cursor: pointer; }
.cat-icon-box { width: 55px; height: 55px; background: white; border: 1px solid #eee; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; transition: 0.3s; margin-bottom: 8px; }
.product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 15px; }
.product-card { background: white; border: 1px solid #eee; border-radius: 12px; overflow: hidden; transition: transform 0.2s; cursor: pointer; }
.product-card:hover { transform: translateY(-5px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
.product-img { height: 160px; background: #f9f9f9; position: relative; }
.product-img img { width: 100%; height: 100%; object-fit: cover; }
.badge-loc { position: absolute; bottom: 5px; left: 5px; background: rgba(0,0,0,0.6); color: white; font-size: 10px; padding: 2px 6px; border-radius: 4px; }
.product-detail { padding: 12px; }
.p-name { font-size: 0.95rem; margin: 0; color: #333; font-weight: 600; text-overflow: ellipsis; overflow: hidden; white-space: nowrap; }
.p-category { font-size: 0.75rem; color: #999; margin: 4px 0; }
.p-price { color: #78866B; font-weight: bold; font-size: 1rem; }
.bottom-nav { position: fixed; bottom: 0; width: 100%; background: white; display: flex; justify-content: space-around; align-items: center; padding: 10px 0; border-top: 1px solid #eee; height: 60px; z-index: 200; }
.nav-link { display: flex; flex-direction: column; align-items: center; font-size: 0.7rem; color: #aaa; cursor: pointer; }
.nav-link.active { color: #78866B; font-weight: bold; }
.plus-btn { width: 55px; height: 55px; background: #78866B; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; box-shadow: 0 4px 10px rgba(120, 134, 107, 0.4); }
@media (min-width: 769px) { .bottom-nav, .mobile-actions { display: none; } .desktop-actions { display: flex; } }
</style>