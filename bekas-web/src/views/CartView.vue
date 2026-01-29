<template>
  <div class="page-container">
    <div class="header">
      <h2>🛒 Keranjang Belanja</h2>
    </div>

    <div v-if="cartItems.length === 0" class="empty-state">
      <p>Keranjangmu masih kosong nih.</p>
      <button @click="router.push('/')" class="btn-shop">Mulai Belanja</button>
    </div>

    <div v-else class="cart-content">
      <div class="select-all-bar">
        <label class="checkbox-container">
          <input type="checkbox" :checked="isAllSelected" @change="toggleSelectAll" />
          <span class="checkmark"></span>
          Pilih Semua ({{ cartItems.length }})
        </label>
      </div>

      <div class="cart-list">
        <div v-for="(item, index) in cartItems" :key="index" class="cart-item">
          <label class="checkbox-container">
            <input type="checkbox" :value="index" v-model="selectedIndexes" />
            <span class="checkmark"></span>
          </label>

          <img :src="getProductImage(item.gambar)" class="item-img" />
          
          <div class="item-info">
            <h3>{{ item.nama_barang }}</h3>
            
            <span class="category-tag">{{ item.kategori || 'Umum' }}</span>
            
            <p class="price">Rp {{ Number(item.harga).toLocaleString('id-ID') }}</p>
          </div>

          <button @click="removeItem(index)" class="btn-remove" title="Hapus">🗑️</button>
        </div>
      </div>

      <div class="summary-box">
        <h3>Ringkasan Belanja</h3>
        <div class="summary-row">
          <span>Barang Terpilih</span>
          <span>{{ selectedIndexes.length }} Item</span>
        </div>
        <div class="summary-row">
          <span>Total Harga</span>
          <span class="total-price">Rp {{ totalPrice.toLocaleString('id-ID') }}</span>
        </div>
        
        <button 
          @click="checkout" 
          class="btn-checkout" 
          :disabled="selectedIndexes.length === 0"
        >
          {{ selectedIndexes.length === 0 ? 'Pilih Barang Terlebih Dahulu' : 'Lanjut Pembayaran' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const cartItems = ref([]);
const selectedIndexes = ref([]); 

onMounted(() => {
  const savedCart = localStorage.getItem('cart');
  if (savedCart) {
    cartItems.value = JSON.parse(savedCart);
    // console.log(cartItems.value); // Bisa dicek di inspect element
  }
});

// Hitung Total Harga (Hanya yang dipilih)
const totalPrice = computed(() => {
  return cartItems.value
    .filter((_, index) => selectedIndexes.value.includes(index))
    // PERBAIKAN 4: reduce pakai item.harga
    .reduce((sum, item) => sum + Number(item.harga), 0);
});

// Logika Select All
const isAllSelected = computed(() => {
  return cartItems.value.length > 0 && selectedIndexes.value.length === cartItems.value.length;
});

const toggleSelectAll = () => {
  if (isAllSelected.value) {
    selectedIndexes.value = [];
  } else {
    selectedIndexes.value = cartItems.value.map((_, index) => index);
  }
};

// Hapus Item
const removeItem = (index) => {
  if (confirm('Hapus barang ini dari keranjang?')) {
    cartItems.value.splice(index, 1);
    selectedIndexes.value = [];
    localStorage.setItem('cart', JSON.stringify(cartItems.value));
  }
};

// Proses Checkout
const checkout = () => {
  const itemsToPay = cartItems.value.filter((_, index) => selectedIndexes.value.includes(index));
  
  if (itemsToPay.length > 0) {
    localStorage.setItem('checkout_item', JSON.stringify(itemsToPay));
    router.push('/payment');
  }
};

const getProductImage = (imgName) => {
  if (!imgName) return 'https://placehold.co/100x100?text=No+Img';
  return imgName.startsWith('http') ? imgName : `http://localhost:8000/storage/${imgName}`;
};
</script>

<style scoped>
.page-container { max-width: 800px; margin: 0 auto; padding: 20px; font-family: 'Inter', sans-serif; }
.header { margin-bottom: 20px; border-bottom: 2px solid #f1f1f1; padding-bottom: 15px; }

.select-all-bar { background: #fff; padding: 15px; border-radius: 10px; margin-bottom: 15px; border: 1px solid #eee; }

/* Checkbox Custom Style */
.checkbox-container { display: flex; align-items: center; cursor: pointer; font-weight: 600; font-size: 0.9rem; }
.checkbox-container input { position: absolute; opacity: 0; cursor: pointer; height: 0; width: 0; }
.checkmark { height: 20px; width: 20px; background-color: #eee; border-radius: 4px; margin-right: 12px; position: relative; }
.checkbox-container:hover input ~ .checkmark { background-color: #ccc; }
.checkbox-container input:checked ~ .checkmark { background-color: #78866B; }
.checkmark:after { content: ""; position: absolute; display: none; left: 7px; top: 3px; width: 5px; height: 10px; border: solid white; border-width: 0 2px 2px 0; transform: rotate(45deg); }
.checkbox-container input:checked ~ .checkmark:after { display: block; }

/* Cart Item */
.cart-list { display: flex; flex-direction: column; gap: 15px; margin-bottom: 30px; }
.cart-item { display: flex; align-items: center; gap: 15px; background: white; padding: 15px; border-radius: 12px; border: 1px solid #eee; transition: 0.2s; }
.cart-item:hover { border-color: #78866B; }
.item-img { width: 90px; height: 90px; object-fit: cover; border-radius: 10px; background: #f9f9f9; }
.item-info { flex: 1; }
.item-info h3 { margin: 0 0 5px 0; font-size: 1rem; color: #333; }
.category-tag { font-size: 0.7rem; background: #f1f1f1; padding: 2px 8px; border-radius: 4px; color: #888; }
.price { font-weight: 800; color: #78866B; margin-top: 8px; font-size: 1.1rem; }
.btn-remove { background: #fff5f5; border: none; cursor: pointer; padding: 8px; border-radius: 8px; transition: 0.2s; }
.btn-remove:hover { background: #fee2e2; }

/* Summary */
.summary-box { background: #fff; padding: 25px; border-radius: 15px; border: 1px solid #eee; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
.summary-row { display: flex; justify-content: space-between; margin-bottom: 12px; color: #666; }
.total-price { color: #d0011b; font-weight: 800; font-size: 1.3rem; }
.btn-checkout { width: 100%; background: #333333; color: white; padding: 16px; border: none; border-radius: 12px; font-weight: bold; cursor: pointer; font-size: 1rem; transition: 0.3s; margin-top: 10px; }
.btn-checkout:hover:not(:disabled) { background: #78866B; transform: translateY(-2px); }
.btn-checkout:disabled { background: #ccc; cursor: not-allowed; }

.empty-state { text-align: center; padding: 60px; background: #f9f9f9; border-radius: 20px; border: 2px dashed #ddd; }
.btn-shop { background: #78866B; color: white; border: none; padding: 12px 25px; border-radius: 8px; cursor: pointer; margin-top: 15px; font-weight: 600; }
</style>