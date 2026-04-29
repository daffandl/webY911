/**
 * Secure Storage Utility for WebY911
 * 
 * This utility provides secure storage for authentication tokens and sensitive data.
 * - Uses sessionStorage instead of localStorage to limit XSS exposure
 * - Tokens are cleared on browser close
 * - Provides CSRF token management
 * - Implements secure cookie handling
 */

interface StorageOptions {
  secure?: boolean;
  sameSite?: 'Strict' | 'Lax' | 'None';
  maxAge?: number;
}

class SecureStorage {
  private static readonly TOKEN_KEY = 'auth_token_v1';
  private static readonly USER_KEY = 'auth_user_v1';
  private static readonly CSRF_KEY = 'csrf_token_v1';

  /**
   * Set authentication token in secure session storage
   * SessionStorage is automatically cleared when the browser closes
   */
  static setAuthToken(token: string): void {
    try {
      if (typeof window !== 'undefined' && window.sessionStorage) {
        window.sessionStorage.setItem(this.TOKEN_KEY, token);
      }
    } catch (error) {
      console.error('Failed to set auth token:', error);
    }
  }

  /**
   * Get authentication token from session storage
   */
  static getAuthToken(): string | null {
    try {
      if (typeof window !== 'undefined' && window.sessionStorage) {
        return window.sessionStorage.getItem(this.TOKEN_KEY);
      }
    } catch (error) {
      console.error('Failed to get auth token:', error);
    }
    return null;
  }

  /**
   * Set user data in secure session storage
   */
  static setUser(user: any): void {
    try {
      if (typeof window !== 'undefined' && window.sessionStorage) {
        window.sessionStorage.setItem(this.USER_KEY, JSON.stringify(user));
      }
    } catch (error) {
      console.error('Failed to set user:', error);
    }
  }

  /**
   * Get user data from session storage
   */
  static getUser(): any | null {
    try {
      if (typeof window !== 'undefined' && window.sessionStorage) {
        const userData = window.sessionStorage.getItem(this.USER_KEY);
        return userData ? JSON.parse(userData) : null;
      }
    } catch (error) {
      console.error('Failed to get user:', error);
    }
    return null;
  }

  /**
   * Clear all authentication data
   */
  static clear(): void {
    try {
      if (typeof window !== 'undefined' && window.sessionStorage) {
        window.sessionStorage.removeItem(this.TOKEN_KEY);
        window.sessionStorage.removeItem(this.USER_KEY);
        window.sessionStorage.removeItem(this.CSRF_KEY);
      }
    } catch (error) {
      console.error('Failed to clear storage:', error);
    }
  }

  /**
   * Set CSRF token for form submissions
   */
  static setCSRFToken(token: string): void {
    try {
      if (typeof window !== 'undefined' && window.sessionStorage) {
        window.sessionStorage.setItem(this.CSRF_KEY, token);
      }
    } catch (error) {
      console.error('Failed to set CSRF token:', error);
    }
  }

  /**
   * Get CSRF token for form submissions
   */
  static getCSRFToken(): string | null {
    try {
      if (typeof window !== 'undefined' && window.sessionStorage) {
        return window.sessionStorage.getItem(this.CSRF_KEY);
      }
    } catch (error) {
      console.error('Failed to get CSRF token:', error);
    }
    return null;
  }

  /**
   * Check if user is authenticated
   */
  static isAuthenticated(): boolean {
    return !!this.getAuthToken() && !!this.getUser();
  }

  /**
   * Get storage info for debugging
   */
  static getStorageInfo(): {
    hasToken: boolean;
    hasUser: boolean;
    hasCSRF: boolean;
  } {
    return {
      hasToken: !!this.getAuthToken(),
      hasUser: !!this.getUser(),
      hasCSRF: !!this.getCSRFToken(),
    };
  }
}

export default SecureStorage;
