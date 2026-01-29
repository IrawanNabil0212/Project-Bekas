<template>
  <div class="auth-page">
    <div class="auth-wrapper">

      <div class="left-panel">
        <div class="brand-content">
          <span style="font-size: 60px;">
            <img src="/logo_bekas_asli.png" alt="Logo" class="logo-img" />
          </span>
          <p>Platform jual beli barang bekas berkualitas, aman, dan terpercaya</p>
        </div>
        <div class="overlay-decor"></div>
      </div>

      <div class="right-panel">
        <div class="form-container">
          <div class="mobile-brand">🛍️ BekasBerkah</div> 
          
          <h1 class="auth-title">Selamat Datang</h1>
          <p class="auth-subtitle">Silakan masuk untuk mengelola akun Anda</p>

          <form @submit.prevent="handleLogin">
            <div class="input-group">
              <label>Email</label>
              <input type="text" v-model="email" placeholder="contoh@email.com" required />
            </div>

            <div class="input-group">
              <label>Password</label>
              <input type="password" v-model="password" placeholder="••••••••" required />
            </div>

            <button type="submit" class="btn-primary" :disabled="isLoading">
              {{ isLoading ? 'Memuat...' : 'MASUK SEKARANG' }}
            </button>
          </form>

          <p class="footer-text">
            Belum punya akun? 
            <span class="link" @click="router.push('/register')">Daftar Disini</span>
          </p>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'; // Mengimpor fungsi reaktif dari Vue
import { useRouter } from 'vue-router'; // Mengimpor router untuk pindah halaman
import api from '../api'; // Mengimpor konfigurasi axios/api

const router = useRouter(); // Inisialisasi router
const email = ref(''); // Variable penampung input email
const password = ref(''); // Variable penampung input password
const isLoading = ref(false); // Status loading untuk tombol

// FUNGSI UTAMA: Menangani proses login
const handleLogin = async () => {
  isLoading.value = true; // Aktifkan status loading
  try {
    // Mengirim data login ke API Laravel backend
    const response = await api.post('/login', {
      email: email.value,
      password: password.value
    });

    // Jika login berhasil
    if (response.data.success) {
      // Simpan token akses ke memory browser (localStorage)
      localStorage.setItem('token', response.data.access_token);
      // Simpan data user (nama, email, dll) dalam bentuk string JSON
      localStorage.setItem('user', JSON.stringify(response.data.data)); 
      
      // Arahkan user ke halaman Beranda
      router.push('/');
    } else {
      // Tampilkan pesan error jika login gagal dari sisi server
      alert(response.data.message || "Login gagal");
    }
  } catch (err) {
    // Jika terjadi error koneksi atau input salah
    console.error(err);
    alert('Email atau password salah!');
  } finally {
    isLoading.value = false; // Matikan status loading kembali
  }
};
</script>

<style scoped>
/* CSS UNTUK TAMPILAN */

.auth-page {
  min-height: 100vh; /* Tinggi minimal seukuran layar penuh */
  background: #f4f6f8;
  display: flex;
  align-items: center; /* Tengah vertikal */
  justify-content: center; /* Tengah horizontal */
  padding: 20px;
}

.auth-wrapper {
  background: white;
  width: 100%;
  max-width: 1000px; /* Lebar maksimal box putih */
  height: 600px; 
  display: flex;
  border-radius: 20px;
  overflow: hidden; /* Supaya isi tidak keluar dari border radius */
  box-shadow: 0 10px 40px rgba(0,0,0,0.08); /* Efek bayangan halus */
}

/* Bagian Panel Kiri (Hijau) */
.left-panel {
  flex: 1;
  background: linear-gradient(135deg, #5a6650, #5a6650); /* Warna background gradasi */
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px;
  position: relative;
}

/* Bagian Panel Kanan (Putih) */
.right-panel {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
  padding: 50px;
  background: white;
}

/* Gaya Input Box */
.input-group input {
  width: 100%; 
  padding: 12px 15px; 
  border: 1px solid #ddd;
  border-radius: 8px; 
  outline: none; 
  transition: 0.3s; 
  box-sizing: border-box; /* Agar padding tidak merusak lebar */
}

/* Efek saat input diklik (fokus) */
.input-group input:focus { 
  border-color: #78866B; 
  background: #fdfdfd; 
}

/* Gaya Tombol Utama */
.btn-primary {
  width: 100%; 
  padding: 14px; 
  background: #78866B; 
  color: white;
  border: none; 
  border-radius: 8px; 
  font-weight: bold;
  cursor: pointer; 
  transition: 0.2s; 
}

/* Efek hover pada tombol */
.btn-primary:hover { 
  background: #657259; 
}

/* Pengaturan Tampilan saat di HP (Layar Kecil) */
@media (max-width: 768px) {
  .left-panel { display: none; } /* Sembunyikan panel kiri di HP */
  .auth-wrapper { height: auto; flex-direction: column; }
  .right-panel { padding: 40px 30px; min-height: 100vh; }
}
</style>