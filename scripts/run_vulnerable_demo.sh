#!/bin/bash
# ==============================================================================
# Runner Skrip Demo Versi Rentan Terisolasi (CatatanSecure - Tugas KSK)
# NIM: 230170187 (Digit Terakhir 7)
# 
# PERINGATAN KEAMANAN (Ketentuan Dosen No. 3 & ADR DEC-05):
# Skrip ini HANYA BOLEH dijalankan pada interface loopback 127.0.0.1:8001.
# DILARANG KERAS mem-bind ke 0.0.0.0 atau membuka akses ke internet publik.
# Database terpisah: database/database_vulnerable.sqlite
# Cookie sesi terpisah: catatan_vuln_session
# ==============================================================================

echo "================================================================="
echo "  MEMULAI SERVER DEMONSTRASI VERSI RENTAN (CATATANSECURE LAB)"
echo "  Interface: 127.0.0.1 (LOOPBACK ONLY - DILARANG 0.0.0.0)"
echo "  Port     : 8001"
echo "  Env File : .env.vulnerable"
echo "  Database : database/database_vulnerable.sqlite"
echo "  Session  : catatan_vuln_session"
echo "================================================================="
echo "Untuk menghentikan server, tekan Ctrl+C"
echo ""

# Jalankan artisan serve dengan bind ketat 127.0.0.1:8001 dan env vulnerable
exec php artisan serve --host=127.0.0.1 --port=8001 --env=vulnerable
