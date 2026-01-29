<template>
  <div class="checkout-page">
    <nav class="navbar">
      <div class="container nav-content">
        <button @click="router.back()" class="btn-back">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
          Kembali
        </button>
        <h1 class="nav-title">Selesaikan Pesanan</h1>
        <div class="nav-spacer"></div>
      </div>
    </nav>

    <main class="container main-layout">
      <div v-if="isLoading" class="loading-state">
        <div class="loader"></div>
        <p>Memuat data...</p>
      </div>

      <div class="grid-container" v-else>
        
        <div class="details-column">
          
          <section class="section-card">
            <div class="section-header">
              <span class="icon-wrapper bg-red">📍</span>
              <h3>Informasi Pengiriman</h3>
            </div>
            
            <div v-if="user && user.address && user.phone" class="address-details">
              <div class="user-badge">Alamat Saya</div>
              <p class="u-name">{{ user.name }} <span class="u-phone">(+62) {{ user.phone }}</span></p>
              <p class="u-address">{{ user.address }}</p>
            </div>
            
            <div v-else class="address-empty">
              <p>⚠️ Informasi pengiriman belum lengkap.</p>
              <button @click="router.push('/profile')" class="btn-link">Lengkapi Profil Sekarang</button>
            </div>
          </section>

          <section class="section-card">
            <div class="section-header">
              <span class="icon-wrapper bg-green">👜</span>
              <h3>Item yang Dibeli</h3>
            </div>
            <div class="product-list">
              <div v-for="(item, index) in items" :key="index" class="product-card">
                <img :src="item.image" alt="Product" class="p-img" @error="$event.target.src='https://placehold.co/100'" />
                <div class="p-info">
                  <h4 class="p-title">{{ item.name }}</h4>
                  <span class="p-tag">{{ item.category || 'Elektronik' }}</span>
                  <p class="p-price">{{ formatRupiah(item.price) }}</p>
                </div>
              </div>
            </div>
          </section>

          <div class="row-flex">
            <section class="section-card flex-1">
              <div class="section-header"><h3>Kurir Pengiriman</h3></div>
              <div class="shipping-box">
                <div class="shipping-main">
                  <strong>Reguler</strong>
                  <span class="price-tag">{{ formatRupiah(shippingCost) }}</span>
                </div>
                <p class="shipping-sub">J&T / JNE (Estimasi 3 - 5 Hari)</p>
              </div>
            </section>
            
            <section class="section-card flex-1">
              <div class="section-header"><h3>Catatan</h3></div>
              <textarea v-model="userMessage" placeholder="Contoh: Titip di satpam ya..." class="custom-textarea"></textarea>
            </section>
          </div>

          <section class="section-card payment-active">
            <div class="section-header">
              <span class="icon-wrapper bg-blue">💵</span>
              <h3>Metode Pembayaran</h3>
            </div>
            <div class="payment-selector">
              <div class="payment-item active">
                <div class="pay-radio"></div>
                <div class="pay-text">
                  <strong>COD (Bayar di Tempat)</strong>
                  <p>Siapkan uang tunai saat kurir sampai.</p>
                </div>
              </div>
            </div>
          </section>
        </div>

        <div class="summary-column">
          <div class="sticky-wrapper">
            <div class="summary-card">
              <h3 class="summary-title">Ringkasan Pembayaran</h3>
              <div class="bill-details">
                <div class="bill-row">
                  <span>Subtotal Produk</span>
                  <span>{{ formatRupiah(subtotal) }}</span>
                </div>
                <div class="bill-row"><span>Ongkos Kirim</span><span>{{ formatRupiah(shippingCost) }}</span></div>
                <div class="bill-row"><span>Biaya Penanganan</span><span>{{ formatRupiah(serviceFee) }}</span></div>
              </div>
              <div class="bill-total">
                <span class="total-label">Total Pembayaran</span>
                <span class="total-value">{{ formatRupiah(grandTotal) }}</span>
              </div>
              
              <button @click="processPayment" class="btn-primary" :disabled="isProcessing || !isProfileComplete">
                <span v-if="!isProcessing">Buat Pesanan Sekarang</span>
                <span v-else class="loader-small"></span>
              </button>
              
              <p v-if="!isProfileComplete" class="error-hint">Lengkapi alamat profil untuk memesan.</p>
              <div class="secure-badge">Jaminan Transaksi Aman</div>
            </div>
          </div>
        </div>

      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();

// --- STATE VARIABLES ---
const items = ref([]);
const user = ref(null);
const isLoading = ref(true);
const isProcessing = ref(false);
const userMessage = ref('');

// Biaya Tambahan
const shippingCost = 15000;
const serviceFee = 1000;

// --- COMPUTED PROPERTIES ---
const subtotal = computed(() => items.value.reduce((sum, item) => sum + Number(item.price), 0));
const grandTotal = computed(() => subtotal.value + shippingCost + serviceFee);

// Cek kelengkapan profil
const isProfileComplete = computed(() => {
  return user.value && user.value.address && user.value.phone;
});

// Format Rupiah
const formatRupiah = (number) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(number);
};

// --- ON MOUNTED ---
onMounted(async () => {
  const token = localStorage.getItem('token');
  
  if (!token) {
    alert("Silakan login terlebih dahulu");
    router.push('/login');
    return;
  }

  try {
    // 1. Ambil Data Checkout dari LocalStorage
    const checkoutData = localStorage.getItem('checkout_item');
    if (checkoutData) {
      const parsed = JSON.parse(checkoutData);
      items.value = Array.isArray(parsed) ? parsed : [parsed];
    } else {
      router.push('/'); 
    }

    // 2. Ambil Data User TERBARU dari Server
    const response = await axios.get('http://localhost:8000/api/me', {
      headers: { Authorization: `Bearer ${token}` }
    });
    
    user.value = response.data;

  } catch (error) {
    console.error("Error fetching data:", error);
    if (error.response && error.response.status === 401) {
       localStorage.removeItem('token');
       router.push('/login');
    }
  } finally {
    isLoading.value = false;
  }
});

// --- FUNGSI PROSES PEMBAYARAN ---
const processPayment = async () => {
  if (!isProfileComplete.value) {
    alert('Mohon lengkapi alamat dan nomor HP di menu Profil!');
    router.push('/profile');
    return;
  }
  
  isProcessing.value = true;
  const token = localStorage.getItem('token');
  
  try {
    // LANGKAH 1: Format Items (Ubah id -> product_id)
    const formattedItems = items.value.map(item => ({
      product_id: item.id,
      quantity: 1,
      price: parseInt(item.price) 
    }));

    // LANGKAH 2: Buat Payload
    const payload = {
      address: user.value.address,
      phone: user.value.phone,
      total_price: parseInt(grandTotal.value), 
      shipping_price: parseInt(shippingCost),
      notes: userMessage.value || '-', 
      payment_method: 'COD',
      status: 'PENDING', // Penting agar tidak error validasi status
      items: formattedItems
    };

    console.log("Mengirim Payload:", payload); 

    // LANGKAH 3: Kirim ke Backend
    await axios.post('http://localhost:8000/api/transactions', payload, {
      headers: { Authorization: `Bearer ${token}` }
    });

    alert(`Pesanan Berhasil Dibuat!`);
    localStorage.removeItem('checkout_item');
    
    // LANGKAH 4: Redirect ke Beranda (Bukan ke Transactions)
    router.push('/'); 

  } catch (error) {
    console.error("Gagal membuat pesanan:", error);
    
    let pesanError = "Terjadi kesalahan sistem.";
    
    // Cek error dari Laravel dengan detail
    if (error.response && error.response.data) {
        if (error.response.data.errors) {
            const detailError = Object.values(error.response.data.errors).flat().join('\n- ');
            pesanError = "Gagal Validasi:\n- " + detailError;
        } else if (error.response.data.message) {
            pesanError = error.response.data.message;
        }
    }

    alert(pesanError); 

  } finally {
    isProcessing.value = false;
  }
};
</script>

<style scoped>
/* --- STYLES --- */
.checkout-page { --primary: #333; --accent: #78866B; --bg: #f8f9fa; background: var(--bg); min-height: 100vh; font-family: 'Inter', sans-serif; color: #333; }
.container { max-width: 1140px; margin: 0 auto; padding: 0 20px; }
.navbar { background: white; border-bottom: 1px solid #eee; padding: 18px 0; position: sticky; top: 0; z-index: 1000; }
.nav-content { display: flex; align-items: center; justify-content: space-between; }
.btn-back { background: none; border: none; cursor: pointer; display: flex; align-items: center; gap: 5px; color: #666; font-weight: 500; }
.nav-title { font-size: 1.2rem; font-weight: 700; }
.nav-spacer { width: 80px; }

.main-layout { padding: 30px 0; }
.loading-state { text-align: center; padding: 50px; color: #666; }
.grid-container { display: grid; grid-template-columns: 1fr; gap: 30px; }
@media (min-width: 992px) { .grid-container { grid-template-columns: 1.8fr 1fr; } }

.section-card { background: white; border-radius: 16px; padding: 24px; margin-bottom: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.04); border: 1px solid #f0f0f0; }
.section-header { display: flex; align-items: center; gap: 15px; margin-bottom: 20px; }
.icon-wrapper { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
.bg-red { background: #fff5f5; }
.bg-green { background: #f0fff4; }
.bg-blue { background: #edf2ff; }

.address-details { padding-left: 5px; }
.u-name { font-weight: 700; font-size: 1.05rem; margin-bottom: 5px; }
.u-phone { font-weight: 400; color: #666; font-size: 0.9rem; }
.u-address { color: #555; font-size: 0.95rem; line-height: 1.5; }
.address-empty { text-align: center; padding: 10px; color: #888; }
.btn-link { background: none; border: none; color: #4c6ef5; text-decoration: underline; cursor: pointer; font-weight: 600; margin-top: 5px; }

.product-card { display: flex; gap: 18px; padding: 15px 0; border-bottom: 1px solid #f8f8f8; }
.product-card:last-child { border-bottom: none; }
.p-img { width: 70px; height: 70px; object-fit: cover; border-radius: 10px; background: #eee; }
.p-info { display: flex; flex-direction: column; justify-content: center; }
.p-title { font-weight: 600; font-size: 0.95rem; margin-bottom: 4px; }
.p-tag { font-size: 0.75rem; background: #eee; align-self: flex-start; padding: 2px 8px; border-radius: 4px; color: #666; margin-bottom: 4px; }
.p-price { font-weight: 700; color: var(--accent); }

.row-flex { display: flex; gap: 20px; flex-wrap: wrap; }
.flex-1 { flex: 1; min-width: 250px; }
.shipping-box { border: 1px solid #eee; border-radius: 12px; padding: 15px; }
.shipping-main { display: flex; justify-content: space-between; margin-bottom: 5px; font-weight: 600; }
.shipping-sub { font-size: 0.85rem; color: #888; }
.custom-textarea { width: 100%; height: 82px; border: 1px solid #eee; border-radius: 10px; padding: 12px; resize: none; font-family: inherit; font-size: 0.9rem; outline: none; transition: border 0.2s; }
.custom-textarea:focus { border-color: var(--accent); }

.payment-item { display: flex; align-items: center; gap: 15px; padding: 15px; border: 2px solid var(--accent); border-radius: 12px; background: #f9fbf8; cursor: pointer; }
.pay-radio { width: 18px; height: 18px; border-radius: 50%; border: 5px solid var(--accent); background: white; }
.pay-text strong { display: block; margin-bottom: 3px; }
.pay-text p { font-size: 0.85rem; color: #666; margin: 0; }

.summary-card { background: white; border-radius: 20px; padding: 25px; box-shadow: 0 10px 40px rgba(0,0,0,0.06); position: sticky; top: 100px; }
.bill-row { display: flex; justify-content: space-between; margin-bottom: 12px; color: #666; font-size: 0.95rem; }
.bill-total { border-top: 1px dashed #ddd; padding-top: 20px; margin-top: 10px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.total-value { font-size: 1.3rem; font-weight: 800; color: #d0011b; }

.btn-primary { width: 100%; background: #222; color: white; border: none; padding: 16px; border-radius: 12px; font-weight: 700; cursor: pointer; transition: background 0.2s; display: flex; justify-content: center; }
.btn-primary:hover { background: #000; }
.btn-primary:disabled { background: #ccc; cursor: not-allowed; }
.error-hint { color: #d0011b; font-size: 0.8rem; text-align: center; margin-top: 10px; }
.secure-badge { text-align: center; font-size: 0.75rem; color: #999; margin-top: 15px; display: flex; align-items: center; justify-content: center; gap: 5px; }
.secure-badge::before { content: "🔒"; }

.loader { width: 40px; height: 40px; border: 4px solid #eee; border-top-color: var(--accent); border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 15px; }
.loader-small { width: 20px; height: 20px; border: 2px solid rgba(255,255,255,0.3); border-top-color: white; border-radius: 50%; animation: spin 0.8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>