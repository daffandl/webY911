#!/bin/bash

# Test script to diagnose dashboard API issues
# Run this from the backend directory

echo "=== Dashboard API Diagnostic ==="
echo ""

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if backend is running
echo "1. Checking if backend is running..."
if curl -s http://localhost:8000/api/bookings/statistics > /dev/null 2>&1; then
    echo -e "${GREEN}✓${NC} Backend is running"
else
    echo -e "${RED}✗${NC} Backend is NOT running! Start it with: php artisan serve"
    exit 1
fi

echo ""
echo "2. Testing public statistics endpoint..."
STATS_RESPONSE=$(curl -s http://localhost:8000/api/bookings/statistics)
echo "Response: $STATS_RESPONSE"

echo ""
echo "3. Creating test user..."
REGISTER_RESPONSE=$(curl -s -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "phone": "081234567890",
    "password": "password123",
    "password_confirmation": "password123"
  }')

echo "Register response: $REGISTER_RESPONSE"

# Extract token
TOKEN=$(echo $REGISTER_RESPONSE | grep -o '"token":"[^"]*' | cut -d'"' -f4)

if [ -z "$TOKEN" ]; then
    echo -e "${YELLOW}⚠${NC} User might already exist. Trying to login..."
    LOGIN_RESPONSE=$(curl -s -X POST http://localhost:8000/api/auth/login \
      -H "Content-Type: application/json" \
      -H "Accept: application/json" \
      -d '{
        "email": "test@example.com",
        "password": "password123"
      }')
    
    echo "Login response: $LOGIN_RESPONSE"
    TOKEN=$(echo $LOGIN_RESPONSE | grep -o '"token":"[^"]*' | cut -d'"' -f4)
fi

if [ -z "$TOKEN" ]; then
    echo -e "${RED}✗${NC} Failed to get authentication token"
    exit 1
fi

echo -e "${GREEN}✓${NC} Got token: ${TOKEN:0:20}..."

echo ""
echo "4. Testing authenticated statistics endpoint..."
AUTH_STATS_RESPONSE=$(curl -s http://localhost:8000/api/auth/statistics \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json")

echo "Response: $AUTH_STATS_RESPONSE"

echo ""
echo "5. Testing authenticated bookings endpoint..."
BOOKINGS_RESPONSE=$(curl -s "http://localhost:8000/api/bookings/my?per_page=5" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json")

echo "Response: $BOOKINGS_RESPONSE"

echo ""
echo "6. Creating a test booking..."
BOOKING_RESPONSE=$(curl -s -X POST http://localhost:8000/api/bookings \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "name": "Test User",
    "phone": "081234567890",
    "email": "test@example.com",
    "car_model": "Range Rover (3rd Gen L322, 2002)",
    "service_type": "Service Berkala (Regular Maintenance)",
    "date": "2026-04-10",
    "notes": "Test booking"
  }')

echo "Booking response: $BOOKING_RESPONSE"

echo ""
echo "7. Checking bookings after creating one..."
BOOKINGS_AFTER=$(curl -s "http://localhost:8000/api/bookings/my?per_page=5" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json")

echo "Response: $BOOKINGS_AFTER"

echo ""
echo "=== Diagnostic Complete ==="
echo ""
echo "Copy these responses to debug the issue."
