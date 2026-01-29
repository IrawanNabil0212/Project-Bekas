<template>
  <div class="orders-container">
    <nav class="navbar">
      <div class="nav-content">
        <button @click="router.push('/')" class="back-btn">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
        </button>
        <h3>Status Pesanan</h3>
      </div>
    </nav>

    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Memuat riwayat transaksi...</p>
    </div>
    
    <div v-else-if="orders.length === 0" class="empty-state">
      <div class="empty-icon">📦</div>
      <h3>Belum ada pesanan</h3>
      <p>Kamu belum pernah checkout barang apapun.</p>
      <button @click="router.push('/')" class="btn-shop">Mulai Belanja</button>
    </div>

    <div v-else class="orders-list">
      <div v-for="order in orders" :key="order.id" class="order-card">
        
        <div class="card-header">
          <div class="order-meta">
            <span class="order-id">#TRX-{{ order.id }}</span>
            <span class="order-date">{{ formatDate(order.created_at) }}</span>
          </div>
          <span :class="['status-badge', getStatusColor(order.status)]">
            {{ order.status }}
          </span>
        </div>

        <div class="tracker">
          <div class="step" :class="{ active: getStatusStep(order.status) >= 1 }">
            <div class="circle">1</div>
            <div class="label">Dibuat</div>
          </div>
          <div class="line" :class="{ active: getStatusStep(order.status) >= 2 }"></div>
          
          <div class="step" :class="{ active: getStatusStep(order.status) >= 2 }">
            <div class="circle">2</div>
            <div class="label">Proses</div>
          </div>
          <div class="line" :class="{ active: getStatusStep(order.status) >= 3 }"></div>
          
          <div class="step" :class="{ active: getStatusStep(order.status) >= 3 }">
            <div class="circle">3</div>
            <div class="label">Dikirim</div>
          </div>
          <div class="line" :class="{ active: getStatusStep(order.status) >= 4 }"></div>
          
          <div class="step" :class="{ active: getStatusStep(order.status) >= 4 }">
            <div class="circle">4</div>
            <div class="label">Selesai</div>
          </div>
        </div>

        <div class="order-info">
          <div class="product-preview">
            <div v-if="order.items && order.items.length > 0">
               <div v-for="(item, idx) in order.items" :key="idx" class="item-row">
                  <span class="item-name">🛒 {{ item.product ? item.product.name : 'Produk Item' }}</span>
                  <span class="item-qty">x{{ item.quantity }}</span>
               </div>
            </div>
            <p v-else class="no-items">Detail item tidak tersedia</p>
          </div>

          <div class="divider"></div>

          <div class="details-row">
             <div class="detail-col">
                <span class="label-text">Alamat Pengiriman</span>
                <p class="val-text address">{{ order.address }}</p>
             </div>
             <div class="detail-col text-right">
                <span class="label-text">Total Bayar ({{ order.payment_method }})</span>
                <p class="val-text price">{{ formatRupiah(order.total_price) }}</p>
             </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const orders = ref([]);
const loading = ref(true);

// --- LOGIC TRACKER ---
// Fungsi ini menentukan pesanan sudah sampai tahap ke berapa (1-4)
const getStatusStep = (status) => {
  const s = status ? status.toUpperCase() : '';
  
  if (s === 'CANCELLED') return 0; // Batal
  if (s === 'SUCCESS' || s === 'COMPLETED') return 4; // Selesai
  if (s === 'SHIPPING' || s === 'ON_DELIVERY') return 3; // Dikirim
  if (s === 'PROCESSED' || s === 'PACKING') return 2; // Proses
  
  return 1; // Default PENDING / UNPAID (Dibuat)
};

const getStatusColor = (status) => {
  const step = getStatusStep(status);
  if (status === 'CANCELLED') return 'bg-red';
  if (step === 4) return 'bg-green';
  if (step === 1) return 'bg-orange';
  return 'bg-blue';
};

// --- FORMATTERS ---
const formatRupiah = (num) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num);

const formatDate = (dateStr) => {
    if(!dateStr) return '';
    const date = new Date(dateStr);
    return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};

// --- FETCH DATA ---
const fetchOrders = async () => {
  const token = localStorage.getItem('token');
  if (!token) {
    router.push('/login');
    return;
  }

  try {
    // Mengambil data dari endpoint transactions yang benar
    const res = await axios.get('http://localhost:8000/api/transactions', {
        headers: { Authorization: `Bearer ${token}` }
    });

    // Handle Laravel Resource Wrapper
    if (res.data.data) {
        orders.value = res.data.data;
    } else {
        orders.value = res.data;
    }
  } catch (err) {
    console.error("Gagal ambil pesanan:", err);
    if(err.response && err.response.status === 401) {
        router.push('/login');
    }
  } finally {
    loading.value = false;
  }
};

onMounted(fetchOrders);
</script>

<style scoped>
/* Base Layout */
.orders-container { max-width: 600px; margin: 0 auto; min-height: 100vh; background: #f8f9fa; font-family: 'Inter', sans-serif; padding-bottom: 40px; }

/* Navbar */
.navbar { background: white; padding: 15px 20px; position: sticky; top: 0; z-index: 100; border-bottom: 1px solid #eee; }
.nav-content { display: flex; align-items: center; gap: 15px; }
.back-btn { background: none; border: none; cursor: pointer; display: flex; align-items: center; color: #333; }
h3 { margin: 0; font-size: 1.1rem; font-weight: 700; }

/* Loading & Empty */
.loading-state, .empty-state { padding: 50px 20px; text-align: center; color: #666; }
.spinner { width: 30px; height: 30px; border: 3px solid #ddd; border-top-color: #333; border-radius: 50%; animation: spin 1s infinite linear; margin: 0 auto 15px; }
@keyframes spin { to { transform: rotate(360deg); } }

.empty-icon { font-size: 3rem; margin-bottom: 10px; display: block; }
.btn-shop { margin-top: 15px; padding: 10px 20px; background: #333; color: white; border: none; border-radius: 8px; cursor: pointer; }

/* Order Card */
.orders-list { padding: 20px; }
.order-card { background: white; border-radius: 16px; padding: 20px; margin-bottom: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #f0f0f0; }

.card-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
.order-meta { display: flex; flex-direction: column; }
.order-id { font-weight: 700; color: #333; font-size: 0.95rem; }
.order-date { font-size: 0.8rem; color: #888; margin-top: 2px; }

.status-badge { font-size: 0.7rem; padding: 4px 10px; border-radius: 20px; font-weight: 600; text-transform: uppercase; }
.bg-orange { background: #fff7e6; color: #d46b08; }
.bg-green { background: #f6ffed; color: #389e0d; }
.bg-blue { background: #e6f7ff; color: #096dd9; }
.bg-red { background: #fff1f0; color: #cf1322; }

/* TRACKER STYLES */
.tracker { display: flex; align-items: center; justify-content: space-between; margin-bottom: 25px; padding: 0 5px; }
.step { display: flex; flex-direction: column; align-items: center; position: relative; z-index: 2; width: 40px; }
.circle { width: 24px; height: 24px; background: white; border: 2px solid #ddd; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 10px; color: #999; font-weight: bold; transition: 0.3s; }
.label { font-size: 10px; color: #aaa; margin-top: 6px; font-weight: 500; }

.step.active .circle { background: #333; border-color: #333; color: white; }
.step.active .label { color: #333; font-weight: 700; }

.line { flex-grow: 1; height: 2px; background: #eee; margin-top: -18px; margin-left: -5px; margin-right: -5px; z-index: 1; transition: 0.3s; }
.line.active { background: #333; }

/* Order Info */
.order-info { background: #fdfdfd; border-radius: 10px; border: 1px solid #f5f5f5; padding: 15px; }
.item-row { display: flex; justify-content: space-between; font-size: 0.9rem; margin-bottom: 6px; color: #555; }
.item-name { font-weight: 500; }
.divider { height: 1px; background: #eee; margin: 10px 0; }

.details-row { display: flex; justify-content: space-between; margin-top: 10px; }
.label-text { font-size: 0.75rem; color: #888; display: block; margin-bottom: 2px; }
.val-text { font-size: 0.9rem; color: #333; font-weight: 600; margin: 0; }
.val-text.price { color: #d0011b; font-size: 1rem; }
.val-text.address { font-size: 0.85rem; font-weight: 400; line-height: 1.3; max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.text-right { text-align: right; }
</style>