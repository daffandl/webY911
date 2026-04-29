'use client';

import React, { createContext, useContext, useState, useEffect, useCallback } from 'react';
import SecureStorage from '@/lib/SecureStorage';

interface User {
  id: number;
  name: string;
  email: string;
  phone: string | null;
  profile_photo: string | null;
  role: string;
  created_at: string;
}

interface AuthContextType {
  user: User | null;
  token: string | null;
  isLoading: boolean;
  apiUrl: string;
  login: (email: string, password: string) => Promise<void>;
  register: (name: string, email: string, phone: string, password: string, passwordConfirmation: string, profilePhoto?: File | null) => Promise<void>;
  logout: () => Promise<void>;
  updateProfile: (name: string, phone: string, profilePhoto?: File | null) => Promise<void>;
  changePassword: (currentPassword: string, password: string, passwordConfirmation: string) => Promise<void>;
  isAuthenticated: boolean;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

const API_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api';

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [user, setUser] = useState<User | null>(null);
  const [token, setToken] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  // Initialize from secure session storage on mount
  useEffect(() => {
    try {
      const storedToken = SecureStorage.getAuthToken();
      const storedUser = SecureStorage.getUser();

      if (storedToken && storedUser) {
        setToken(storedToken);
        setUser(storedUser);
      }
    } catch (error) {
      console.error('Failed to restore auth state:', error);
    } finally {
      setIsLoading(false);
    }
  }, []);

  const login = useCallback(async (email: string, password: string) => {
    setIsLoading(true);
    try {
      const response = await fetch(`${API_URL}/auth/login`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        credentials: 'include', // Include cookies for HttpOnly cookie support
        body: JSON.stringify({ email, password }),
      });

      const contentType = response.headers.get('content-type') || '';
      if (!contentType.includes('application/json')) {
        const text = await response.text();
        console.error('Non-JSON response received:', text.substring(0, 500));
        throw new Error('Server tidak merespon dengan benar. Pastikan backend sudah berjalan.');
      }

      const data = await response.json();

      if (!response.ok) {
        // Handle validation errors from backend
        if (data.errors && typeof data.errors === 'object') {
          const errorMessages = Object.values(data.errors).flat().join('\n');
          throw new Error(errorMessages || data.message || 'Terjadi kesalahan validasi');
        }
        throw new Error(data.message || 'Terjadi kesalahan');
      }

      if (!data.success) {
        // Handle validation errors from backend
        if (data.errors && typeof data.errors === 'object') {
          const errorMessages = Object.values(data.errors).flat().join('\n');
          throw new Error(errorMessages || data.message || 'Terjadi kesalahan validasi');
        }
        throw new Error(data.message || 'Terjadi kesalahan');
      }

      const { user: userData, token: newToken } = data.data;
      setUser(userData);
      setToken(newToken);
      
      // Store in secure session storage (auto-cleared on browser close)
      SecureStorage.setAuthToken(newToken);
      SecureStorage.setUser(userData);
    } catch (error: any) {
      // Clear storage on error
      SecureStorage.clear();
      setUser(null);
      setToken(null);
      throw error;
    } finally {
      setIsLoading(false);
    }
  }, []);

  const register = useCallback(async (name: string, email: string, phone: string, password: string, passwordConfirmation: string, profilePhoto?: File | null) => {
    setIsLoading(true);
    try {
      // Use FormData if profile photo is provided, otherwise JSON
      let response: Response;
      
      if (profilePhoto) {
        const formData = new FormData();
        formData.append('name', name);
        formData.append('email', email);
        formData.append('phone', phone);
        formData.append('password', password);
        formData.append('password_confirmation', passwordConfirmation);
        formData.append('profile_photo', profilePhoto);
        
        response = await fetch(`${API_URL}/auth/register`, {
          method: 'POST',
          headers: {
            'Accept': 'application/json',
          },
          credentials: 'include',
          body: formData,
        });
      } else {
        response = await fetch(`${API_URL}/auth/register`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
          },
          credentials: 'include',
          body: JSON.stringify({ name, email, phone, password, password_confirmation: passwordConfirmation }),
        });
      }

      const contentType = response.headers.get('content-type') || '';
      if (!contentType.includes('application/json')) {
        const text = await response.text();
        console.error('Non-JSON response received:', text.substring(0, 500));
        throw new Error('Server tidak merespon dengan benar. Pastikan backend sudah berjalan.');
      }

      const data = await response.json();

      if (!response.ok) {
        // Handle validation errors from backend
        if (data.errors && typeof data.errors === 'object') {
          const errorMessages = Object.values(data.errors).flat().join('\n');
          throw new Error(errorMessages || data.message || 'Terjadi kesalahan validasi');
        }
        throw new Error(data.message || 'Terjadi kesalahan');
      }

      if (!data.success) {
        // Handle validation errors from backend
        if (data.errors && typeof data.errors === 'object') {
          const errorMessages = Object.values(data.errors).flat().join('\n');
          throw new Error(errorMessages || data.message || 'Terjadi kesalahan validasi');
        }
        throw new Error(data.message || 'Terjadi kesalahan');
      }

      const { user: userData, token: newToken } = data.data;
      setUser(userData);
      setToken(newToken);
      
      // Store in secure session storage
      SecureStorage.setAuthToken(newToken);
      SecureStorage.setUser(userData);
    } catch (error: any) {
      SecureStorage.clear();
      setUser(null);
      setToken(null);
      throw error;
    } finally {
      setIsLoading(false);
    }
  }, []);

  const logout = useCallback(async () => {
    if (!token) return;

    try {
      await fetch(`${API_URL}/auth/logout`, {
        method: 'POST',
        headers: { Authorization: `Bearer ${token}` },
        credentials: 'include',
      });
    } catch (error) {
      console.error('Logout error:', error);
    } finally {
      setUser(null);
      setToken(null);
      SecureStorage.clear();
    }
  }, [token]);

  const updateProfile = useCallback(async (name: string, phone: string, profilePhoto?: File | null) => {
    if (!token || !user) return;

    let response: Response;
    
    if (profilePhoto) {
      const formData = new FormData();
      formData.append('name', name);
      formData.append('phone', phone);
      formData.append('profile_photo', profilePhoto);
      formData.append('_method', 'PUT');
      
      response = await fetch(`${API_URL}/auth/profile`, {
        method: 'POST',
        headers: {
          Authorization: `Bearer ${token}`,
          'Accept': 'application/json',
        },
        credentials: 'include',
        body: formData,
      });
    } else {
      response = await fetch(`${API_URL}/auth/profile`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`,
          'Accept': 'application/json',
        },
        credentials: 'include',
        body: JSON.stringify({ name, phone }),
      });
    }

    const data = await response.json();
    if (!response.ok) {
      throw new Error(data.message || 'Gagal memperbarui profil');
    }

    const updatedUser = data.data;
    setUser(updatedUser);
    SecureStorage.setUser(updatedUser);
  }, [token, user]);

  const changePassword = useCallback(async (currentPassword: string, password: string, passwordConfirmation: string) => {
    if (!token) return;

    const response = await fetch(`${API_URL}/auth/change-password`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${token}`,
        'Accept': 'application/json',
      },
      credentials: 'include',
      body: JSON.stringify({
        current_password: currentPassword,
        password,
        password_confirmation: passwordConfirmation,
      }),
    });

    const data = await response.json();
    if (!response.ok) {
      const errorMsg = data.message || Object.values(data.errors || {}).flat().join(', ');
      throw new Error(errorMsg || 'Gagal mengubah password');
    }
  }, [token]);

  return (
    <AuthContext.Provider
      value={{
        user,
        token,
        isLoading,
        apiUrl: API_URL,
        login,
        register,
        logout,
        updateProfile,
        changePassword,
        isAuthenticated: !!user && !!token,
      }}
    >
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
}
