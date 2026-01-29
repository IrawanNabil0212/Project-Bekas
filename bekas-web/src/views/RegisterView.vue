<template>
  <div class="auth-page">
    <div class="auth-wrapper">

      <div class="left-panel">
        <div class="brand-content">
          <span style="font-size: 60px;">          
            <img src="/logo_bekas_asli.png" alt="Logo" class="logo-img" />
          </span>
          <h2>Gabung Sekarang</h2>
          <p>Daftarkan diri Anda dan mulai berjualan barang bekas dengan mudah.</p>
        </div>
      </div>

      <div class="right-panel">
        <div class="form-container">
          <div class="mobile-brand">🛍️ BekasBerkah</div>
          
          <h1 class="auth-title">Buat Akun Baru</h1>
          <p class="auth-subtitle">Lengkapi data diri untuk mendaftar</p>

          <form @submit.prevent="handleRegister">
            <div class="input-group">
              <label>Nama Lengkap</label>
              <input type="text" v-model="form.name" placeholder="Nama Anda" required />
            </div>

            <div class="input-group">
              <label>Email</label>
              <input type="email" v-model="form.email" placeholder="contoh@email.com" required />
            </div>

            <div class="input-group">
              <label>Password</label>
              <input type="password" v-model="form.password" placeholder="Minimal 8 karakter" required />
            </div>

            <button type="submit" class="btn-primary" :disabled="loading">
              {{ loading ? 'Mendaftarkan...' : 'DAFTAR SEKARANG' }}
            </button>
          </form>

          <p class="footer-text">
            Sudah punya akun? 
            <span class="link" @click="router.push('/login')">Login Disini</span>
          </p>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '../api'; // Gunakan API instance yang sama

const router = useRouter();
const loading = ref(false);
const form = reactive({ name: '', email: '', password: '' });

const handleRegister = async () => {
  loading.value = true;
  try {
    // Sesuaikan endpoint dengan Laravel
    await api.post('/register', form); 
    alert("Pendaftaran Berhasil! Silakan Login.");
    router.push('/login');
  } catch (err) {
    console.error(err);
    if(err.response && err.response.data.errors) {
       // Tampilkan error spesifik dari Laravel jika ada (misal: email duplicate)
       alert("Gagal: " + JSON.stringify(err.response.data.errors));
    } else {
       alert("Gagal daftar. Email mungkin sudah dipakai.");
    }
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
/* Gunakan Style yang SAMA PERSIS dengan LoginView agar konsisten */
.auth-page { min-height: 100vh; background: #f4f6f8; display: flex; align-items: center; justify-content: center; padding: 20px; }
.auth-wrapper { background: white; width: 100%; max-width: 1000px; height: 600px; display: flex; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.08); }
.left-panel { flex: 1; background: linear-gradient(135deg, #5a6650, #5a6650); color: white; display: flex; align-items: center; justify-content: center; padding: 40px; }
.brand-content { text-align: center; }
.brand-content h2 { font-size: 2rem; margin: 10px 0; }
.right-panel { flex: 1; display: flex; flex-direction: column; justify-content: center; padding: 50px; background: white; }
.mobile-brand { display: none; font-size: 1.5rem; font-weight: bold; color: #78866B; margin-bottom: 20px; text-align: center; }
.auth-title { font-size: 1.8rem; color: #333; margin: 0 0 10px; }
.auth-subtitle { color: #888; margin-bottom: 25px; font-size: 0.9rem; }
.input-group { margin-bottom: 15px; }
.input-group label { display: block; font-size: 0.85rem; font-weight: 600; color: #555; margin-bottom: 5px; }
.input-group input { width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 8px; outline: none; transition: 0.3s; box-sizing: border-box; }
.input-group input:focus { border-color: #78866B; background: #fdfdfd; }
.btn-primary { width: 100%; padding: 14px; background: #78866B; color: white; border: none; border-radius: 8px; font-weight: bold; font-size: 1rem; cursor: pointer; transition: 0.2s; margin-top: 10px; }
.btn-primary:hover { background: #657259; }
.btn-primary:disabled { background: #ccc; cursor: not-allowed; }
.footer-text { text-align: center; margin-top: 20px; color: #666; font-size: 0.9rem; }
.link { color: #78866B; font-weight: bold; cursor: pointer; text-decoration: underline; }

@media (max-width: 768px) {
  .auth-page { padding: 0; background: white; align-items: flex-start; }
  .auth-wrapper { height: auto; border-radius: 0; box-shadow: none; flex-direction: column; }
  .left-panel { display: none; }
  .right-panel { padding: 40px 30px; min-height: 100vh; justify-content: flex-start; }
  .mobile-brand { display: block; margin-top: 10px; }
}
</style>