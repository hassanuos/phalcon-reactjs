import axios from 'axios';
axios.get();
var axiosInstance = axios.create({
  baseURL: 'http://localhost:9900',
  mode: 'no-cors',
  headers: {
    'Access-Control-Allow-Origin': '*',
    'Content-Type': 'application/json',
  },
  withCredentials: false,
  credentials: 'same-origin',
});

export default axiosInstance;