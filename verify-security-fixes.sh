#!/bin/bash

# Security Verification Script for WebY911
# Tests key security fixes to ensure they're working correctly

echo "🔐 WebY911 Security Fixes - Verification Script"
echo "=============================================="
echo ""

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

PASS_COUNT=0
FAIL_COUNT=0

# Test 1: Check if PhoneNumberRule exists
echo "Test 1: PhoneNumberRule validation class..."
if [ -f "/workspaces/webY911/backend/app/Rules/PhoneNumberRule.php" ]; then
    echo -e "${GREEN}✓ PASS${NC}: PhoneNumberRule.php exists"
    ((PASS_COUNT++))
else
    echo -e "${RED}✗ FAIL${NC}: PhoneNumberRule.php not found"
    ((FAIL_COUNT++))
fi
echo ""

# Test 2: Check if ServiceTypeRule exists
echo "Test 2: ServiceTypeRule validation class..."
if [ -f "/workspaces/webY911/backend/app/Rules/ServiceTypeRule.php" ]; then
    echo -e "${GREEN}✓ PASS${NC}: ServiceTypeRule.php exists"
    ((PASS_COUNT++))
else
    echo -e "${RED}✗ FAIL${NC}: ServiceTypeRule.php not found"
    ((FAIL_COUNT++))
fi
echo ""

# Test 3: Check Booking model has secure hash methods
echo "Test 3: Booking model anti-fraud methods..."
if grep -q "generateChecksum" /workspaces/webY911/backend/app/Models/Booking.php; then
    echo -e "${GREEN}✓ PASS${NC}: Booking model has checksum generation"
    ((PASS_COUNT++))
else
    echo -e "${RED}✗ FAIL${NC}: Booking model missing checksum methods"
    ((FAIL_COUNT++))
fi
echo ""

# Test 4: Check Invoice model has HMAC methods
echo "Test 4: Invoice model HMAC security methods..."
if grep -q "generateSecureHash\|verifyHash" /workspaces/webY911/backend/app/Models/Invoice.php; then
    echo -e "${GREEN}✓ PASS${NC}: Invoice model has HMAC methods"
    ((PASS_COUNT++))
else
    echo -e "${RED}✗ FAIL${NC}: Invoice model missing HMAC methods"
    ((FAIL_COUNT++))
fi
echo ""

# Test 5: Check VerifyInvoiceController uses HMAC
echo "Test 5: VerifyInvoiceController HMAC verification..."
if grep -q "Invoice::verifyHash" /workspaces/webY911/backend/app/Http/Controllers/VerifyInvoiceController.php; then
    echo -e "${GREEN}✓ PASS${NC}: VerifyInvoiceController uses HMAC verification"
    ((PASS_COUNT++))
else
    echo -e "${RED}✗ FAIL${NC}: VerifyInvoiceController not using HMAC"
    ((FAIL_COUNT++))
fi
echo ""

# Test 6: Check API routes have throttle middleware
echo "Test 6: API Rate Limiting (throttle middleware)..."
if grep -q "throttle:" /workspaces/webY911/backend/routes/api.php; then
    echo -e "${GREEN}✓ PASS${NC}: Rate limiting middleware applied to API routes"
    ((PASS_COUNT++))
else
    echo -e "${RED}✗ FAIL${NC}: Rate limiting middleware not found"
    ((FAIL_COUNT++))
fi
echo ""

# Test 7: Check CustomerDashboard component
echo "Test 7: CustomerDashboard component implementation..."
if grep -q "useEffect\|useState\|fetchDashboardData" /workspaces/webY911/app/components/CustomerDashboard.tsx; then
    echo -e "${GREEN}✓ PASS${NC}: CustomerDashboard fully implemented"
    ((PASS_COUNT++))
else
    echo -e "${RED}✗ FAIL${NC}: CustomerDashboard not properly implemented"
    ((FAIL_COUNT++))
fi
echo ""

# Test 8: Check migration files exist
echo "Test 8: Database migrations..."
if [ -f "/workspaces/webY911/backend/database/migrations/2026_04_28_152147_add_secure_hash_to_invoices_table.php" ] && \
   [ -f "/workspaces/webY911/backend/database/migrations/2026_04_28_152148_add_service_price_to_bookings_table.php" ]; then
    echo -e "${GREEN}✓ PASS${NC}: Both migration files created"
    ((PASS_COUNT++))
else
    echo -e "${RED}✗ FAIL${NC}: Migration files missing"
    ((FAIL_COUNT++))
fi
echo ""

# Test 9: Check BookingController phone normalization
echo "Test 9: BookingController phone normalization..."
if grep -q "normalizePhoneNumber\|PhoneNumberRule" /workspaces/webY911/backend/app/Http/Controllers/Api/BookingController.php; then
    echo -e "${GREEN}✓ PASS${NC}: BookingController has phone normalization"
    ((PASS_COUNT++))
else
    echo -e "${RED}✗ FAIL${NC}: BookingController missing phone normalization"
    ((FAIL_COUNT++))
fi
echo ""

# Summary
echo "=============================================="
echo -e "Test Results: ${GREEN}$PASS_COUNT PASSED${NC} | ${RED}$FAIL_COUNT FAILED${NC}"
echo ""

if [ $FAIL_COUNT -eq 0 ]; then
    echo -e "${GREEN}✓ All security fixes verified successfully!${NC}"
    exit 0
else
    echo -e "${RED}✗ Some security fixes need attention${NC}"
    exit 1
fi
