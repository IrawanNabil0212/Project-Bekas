import axios from 'axios';

const api = axios.create({
  // Pastikan URL ini sesuai dengan terminal Laravel kamu
  baseURL: 'http://localhost:8000/api', 
  
  // PENTING: Bagian headers 'Content-Type' SAYA HAPUS.
  // Biarkan Axios yang menentukan apakah itu JSON atau File (Multipart).
  // Jangan ditulis manual disini.
});

// --- INTERCEPTOR (Satpam Token) ---
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token'); 
    
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

export default api;