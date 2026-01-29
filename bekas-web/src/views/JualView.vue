<template>
  <div class="page-background">
    
    <header class="simple-header">
      <div class="header-inner">
        <button class="btn-back" @click="router.go(-1)">← Kembali</button>
        <h1 class="header-title">Pasang Iklan Baru</h1>
        <div style="width: 80px;"></div> 
      </div>
    </header>

    <div class="form-wrapper">
      <form @submit.prevent="uploadBarang" class="main-form">
        
        <div class="form-section left-section">
          <label class="input-label">Foto Produk Utama</label>
          <div class="image-upload-card" @click="triggerFileInput">
            <div v-if="imagePreview" class="image-preview-box">
              <img :src="imagePreview" class="img-display" />
              <div class="overlay-change">Ganti Foto</div>
            </div>
            <div v-else class="placeholder-box">
              <span style="font-size: 50px;">📷</span>
              <span class="text-upload">Klik untuk Upload</span>
              <span class="text-hint">Format: JPG, PNG (Max 5MB)</span>
            </div>
            <input type="file" ref="fileInput" class="hidden-input" accept="image/*" @change="handleFileChange" />
          </div>
        </div>

        <div class="form-section right-section">
          
          <div class="form-group">
            <label class="input-label">Nama Barang</label>
            <input v-model="formData.name" type="text" class="input-field" placeholder="Contoh: Laptop Asus ROG Bekas" required />
          </div>

          <div class="form-row">
            <div class="form-group half">
              <label class="input-label">Kategori</label>
              <select v-model="formData.category" class="input-field" required>
                <option value="">Pilih Kategori</option>
                <option value="Gadget">Gadget</option>
                <option value="Fashion">Fashion</option>
                <option value="Otomotif">Otomotif</option>
                <option value="Hobi">Hobi</option>
              </select>
            </div>

            <div class="form-group half">
              <label class="input-label">Harga (Rp)</label>
              <input v-model="formData.price" type="number" class="input-field" placeholder="0" required />
            </div>
          </div>

          <div class="form-group">
            <label class="input-label">Lokasi</label>
            <input v-model="formData.location" type="text" class="input-field" placeholder="Contoh: Yogyakarta, Sleman" required />
          </div>

          <div class="form-group">
            <label class="input-label">Deskripsi Lengkap</label>
            <textarea v-model="formData.description" class="input-field textarea-field" placeholder="Jelaskan kondisi barang, minus, kelengkapan..." required></textarea>
          </div>

          <button type="submit" class="btn-submit" :disabled="loading">
            {{ loading ? 'Sedang Mengupload...' : 'TAYANGKAN IKLAN SEKARANG' }}
          </button>
        </div>

      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import api from '../api'; 

const router = useRouter();
const fileInput = ref(null);
const imagePreview = ref(null);
const imageFile = ref(null);
const loading = ref(false);

const formData = reactive({
  name: '',
  category: '',
  price: '',
  location: '',
  description: ''
});

const triggerFileInput = () => { fileInput.value.click(); };

const handleFileChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    imageFile.value = file;
    imagePreview.value = URL.createObjectURL(file);
  }
};

const uploadBarang = async () => {
  if (!imageFile.value) {
    alert("Wajib upload foto produk!");
    return;
  }
  
  loading.value = true;

  const data = new FormData();
  data.append('name', formData.name);
  data.append('category', formData.category);
  data.append('price', formData.price);
  data.append('location', formData.location);
  data.append('description', formData.description);
  data.append('image', imageFile.value);

  try {
    // Memastikan token dikirim jika route ini butuh login (middleware auth:sanctum)
    const token = localStorage.getItem('token');
    
    await api.post('/products', data, {
        headers: { 
          'Content-Type': 'multipart/form-data',
          'Authorization': `Bearer ${token}` 
        }
    });

    alert("Iklan Berhasil Ditayangkan!");
    router.push('/');

  } catch (err) {
    console.error("Upload Error:", err);
    if (err.response && err.response.status === 401) {
       alert("Sesi berakhir, silakan login kembali.");
       router.push('/login');
    } else if (err.response && err.response.data.errors) {
       const errors = err.response.data.errors;
       let msg = "Gagal:\n";
       for (const k in errors) msg += `- ${errors[k][0]}\n`;
       alert(msg);
    } else {
       alert("Gagal upload. Cek ukuran gambar (maks 2MB) atau koneksi server.");
    }
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
/* Gunakan CSS asli Anda (Sudah Sangat Bagus dan Responsif) */
.page-background { background: #f4f6f8; min-height: 100vh; display: flex; flex-direction: column; }
.simple-header { background: white; border-bottom: 1px solid #ddd; padding: 15px 0; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
.header-inner { max-width: 900px; margin: 0 auto; padding: 0 20px; display: flex; align-items: center; justify-content: space-between; }
.header-title { font-size: 1.2rem; margin: 0; color: #333; font-weight: bold; }
.btn-back { background: none; border: none; font-size: 1rem; cursor: pointer; color: #666; font-weight: 500; }
.btn-back:hover { color: #78866B; }
.form-wrapper { flex: 1; padding: 30px 20px; display: flex; justify-content: center; }
.main-form { background: white; width: 100%; max-width: 900px; padding: 30px; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); display: flex; gap: 40px; }
.left-section { flex: 1; }
.right-section { flex: 1.5; }
.form-group { margin-bottom: 20px; }
.form-row { display: flex; gap: 15px; }
.form-row .half { flex: 1; }
.input-label { display: block; font-weight: 600; font-size: 0.9rem; color: #444; margin-bottom: 8px; }
.input-field { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; box-sizing: border-box; transition: 0.2s; }
.input-field:focus { border-color: #78866B; outline: none; box-shadow: 0 0 0 3px rgba(120, 134, 107, 0.1); }
.textarea-field { height: 120px; resize: vertical; }
.image-upload-card { width: 100%; aspect-ratio: 1 / 1; background: #fafafa; border: 2px dashed #ccc; border-radius: 12px; display: flex; align-items: center; justify-content: center; cursor: pointer; overflow: hidden; position: relative; transition: 0.2s; }
.image-upload-card:hover { border-color: #78866B; background: #f0fdf4; }
.placeholder-box { display: flex; flex-direction: column; align-items: center; color: #aaa; text-align: center; padding: 20px; }
.text-upload { font-weight: bold; margin-top: 10px; color: #666; }
.img-display { width: 100%; height: 100%; object-fit: cover; }
.overlay-change { position: absolute; bottom: 0; width: 100%; background: rgba(0,0,0,0.6); color: white; text-align: center; padding: 8px; font-size: 12px; }
.hidden-input { display: none; }
.btn-submit { width: 100%; padding: 15px; background: #78866B; color: white; border: none; border-radius: 8px; font-weight: bold; font-size: 1rem; cursor: pointer; margin-top: 10px; transition: 0.2s; }
.btn-submit:hover:not(:disabled) { background: #5e6b52; transform: translateY(-2px); }
.btn-submit:disabled { background: #ccc; cursor: not-allowed; }

@media (max-width: 768px) {
  .main-form { flex-direction: column; padding: 20px; gap: 20px; }
  .image-upload-card { aspect-ratio: 16 / 9; }
  .form-row { flex-direction: column; gap: 0; }
}
</style>