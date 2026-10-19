// Firebase Configuration for Bina Ragam Shop
// Replace with your Firebase project configuration

const firebaseConfig = {
    apiKey: "YOUR_API_KEY",
    authDomain: "YOUR_PROJECT_ID.firebaseapp.com",
    projectId: "YOUR_PROJECT_ID",
    storageBucket: "YOUR_PROJECT_ID.appspot.com",
    messagingSenderId: "YOUR_SENDER_ID",
    appId: "YOUR_APP_ID",
    measurementId: "YOUR_MEASUREMENT_ID"
};

// Initialize Firebase
const app = firebase.initializeApp(firebaseConfig);
const auth = firebase.auth();
const db = firebase.firestore();
const storage = firebase.storage();

// Firebase Authentication Functions
class FirebaseAuth {
    static async register(email, password, displayName) {
        try {
            const userCredential = await auth.createUserWithEmailAndPassword(email, password);
            await userCredential.user.updateProfile({ displayName: displayName });
            
            // Create user document in Firestore
            await db.collection('users').doc(userCredential.user.uid).set({
                email: email,
                displayName: displayName,
                role: 'user',
                createdAt: firebase.firestore.FieldValue.serverTimestamp(),
                isActive: true
            });
            
            return { success: true, user: userCredential.user };
        } catch (error) {
            return { success: false, error: error.message };
        }
    }
    
    static async login(email, password) {
        try {
            const userCredential = await auth.signInWithEmailAndPassword(email, password);
            
            // Get user role from Firestore
            const userDoc = await db.collection('users').doc(userCredential.user.uid).get();
            const userData = userDoc.data();
            
            // Store user data in localStorage
            localStorage.setItem('userData', JSON.stringify({
                uid: userCredential.user.uid,
                email: userCredential.user.email,
                displayName: userCredential.user.displayName,
                role: userData.role,
                isActive: userData.isActive
            }));
            
            return { success: true, user: userCredential.user, role: userData.role };
        } catch (error) {
            return { success: false, error: error.message };
        }
    }
    
    static async logout() {
        try {
            await auth.signOut();
            localStorage.removeItem('userData');
            return { success: true };
        } catch (error) {
            return { success: false, error: error.message };
        }
    }
    
    static async checkAuthStatus() {
        return new Promise((resolve) => {
            auth.onAuthStateChanged(async (user) => {
                if (user) {
                    const userDoc = await db.collection('users').doc(user.uid).get();
                    const userData = userDoc.data();
                    resolve({
                        isAuthenticated: true,
                        user: user,
                        role: userData?.role || 'user'
                    });
                } else {
                    resolve({
                        isAuthenticated: false,
                        user: null,
                        role: null
                    });
                }
            });
        });
    }
}

// Firebase Firestore Functions
class FirebaseDB {
    static async addProduct(productData) {
        try {
            const docRef = await db.collection('products').add({
                ...productData,
                createdAt: firebase.firestore.FieldValue.serverTimestamp(),
                updatedAt: firebase.firestore.FieldValue.serverTimestamp()
            });
            return { success: true, id: docRef.id };
        } catch (error) {
            return { success: false, error: error.message };
        }
    }
    
    static async getProducts(filters = {}) {
        try {
            let query = db.collection('products');
            
            // Apply filters
            if (filters.category && filters.category !== 'all') {
                query = query.where('category', '==', filters.category);
            }
            if (filters.search) {
                // Note: Firestore doesn't support full-text search natively
                // This is a simple implementation
                query = query.where('name', '>=', filters.search)
                           .where('name', '<=', filters.search + '\uf8ff');
            }
            if (filters.minPrice) {
                query = query.where('price', '>=', parseInt(filters.minPrice));
            }
            if (filters.maxPrice) {
                query = query.where('price', '<=', parseInt(filters.maxPrice));
            }
            if (filters.inStock === true) {
                query = query.where('stock', '>', 0);
            }
            
            const snapshot = await query.get();
            const products = [];
            
            snapshot.forEach((doc) => {
                products.push({
                    id: doc.id,
                    ...doc.data()
                });
            });
            
            return { success: true, products: products };
        } catch (error) {
            return { success: false, error: error.message };
        }
    }
    
    static async getProduct(id) {
        try {
            const doc = await db.collection('products').doc(id).get();
            if (doc.exists) {
                return { success: true, product: { id: doc.id, ...doc.data() } };
            } else {
                return { success: false, error: 'Product not found' };
            }
        } catch (error) {
            return { success: false, error: error.message };
        }
    }
    
    static async updateProduct(id, productData) {
        try {
            await db.collection('products').doc(id).update({
                ...productData,
                updatedAt: firebase.firestore.FieldValue.serverTimestamp()
            });
            return { success: true };
        } catch (error) {
            return { success: false, error: error.message };
        }
    }
    
    static async deleteProduct(id) {
        try {
            await db.collection('products').doc(id).delete();
            return { success: true };
        } catch (error) {
            return { success: false, error: error.message };
        }
    }
    
    static async incrementProductClicks(productId, platform) {
        try {
            const field = platform === 'shopee' ? 'shopeeClicks' : 'tokopediaClicks';
            await db.collection('products').doc(productId).update({
                [field]: firebase.firestore.FieldValue.increment(1)
            });
            return { success: true };
        } catch (error) {
            return { success: false, error: error.message };
        }
    }
    
    static async addContactMessage(messageData) {
        try {
            await db.collection('contactMessages').add({
                ...messageData,
                createdAt: firebase.firestore.FieldValue.serverTimestamp(),
                status: 'unread'
            });
            return { success: true };
        } catch (error) {
            return { success: false, error: error.message };
        }
    }
    
    static async getDashboardStats() {
        try {
            const productsSnapshot = await db.collection('products').get();
            const usersSnapshot = await db.collection('users').get();
            const messagesSnapshot = await db.collection('contactMessages').get();
            
            let totalShopeeClicks = 0;
            let totalTokopediaClicks = 0;
            
            productsSnapshot.forEach((doc) => {
                const data = doc.data();
                totalShopeeClicks += data.shopeeClicks || 0;
                totalTokopediaClicks += data.tokopediaClicks || 0;
            });
            
            return {
                success: true,
                stats: {
                    totalProducts: productsSnapshot.size,
                    totalUsers: usersSnapshot.size,
                    totalMessages: messagesSnapshot.size,
                    totalShopeeClicks: totalShopeeClicks,
                    totalTokopediaClicks: totalTokopediaClicks
                }
            };
        } catch (error) {
            return { success: false, error: error.message };
        }
    }
}

// Firebase Storage Functions
class FirebaseStorage {
    static async uploadImage(file, path) {
        try {
            const storageRef = storage.ref();
            const fileRef = storageRef.child(path + '/' + Date.now() + '_' + file.name);
            
            const snapshot = await fileRef.put(file);
            const downloadURL = await snapshot.ref.getDownloadURL();
            
            return { success: true, url: downloadURL };
        } catch (error) {
            return { success: false, error: error.message };
        }
    }
    
    static async deleteImage(url) {
        try {
            const storageRef = storage.refFromURL(url);
            await storageRef.delete();
            return { success: true };
        } catch (error) {
            return { success: false, error: error.message };
        }
    }
}

// Export functions for use in other files
window.FirebaseAuth = FirebaseAuth;
window.FirebaseDB = FirebaseDB;
window.FirebaseStorage = FirebaseStorage;