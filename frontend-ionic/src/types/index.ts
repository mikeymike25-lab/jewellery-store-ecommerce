export interface User {
  id: number;
  name: string;
  email: string;
  phone?: string | null;
  street?: string | null;
  barangay?: string | null;
  city?: string | null;
  province?: string | null;
  region?: string | null;
  zip_code?: string | null;
  created_at?: string;
}

export interface Category {
  id: number;
  name: string;
  slug: string;
  description?: string | null;
  image_url?: string | null;
  products_count?: number;
}

export interface ProductImage {
  id?: number;
  image_url: string;
  is_primary?: boolean;
}

export interface Product {
  id: number;
  name: string;
  slug: string;
  sku: string;
  description?: string | null;
  category_id: number;
  category?: Category;
  price?: number;
  base_price: number;
  sale_price?: number | null;
  final_price?: number;
  is_on_sale?: boolean;
  discount_percentage?: number | null;
  stock_quantity: number;
  metal_type?: string | null;
  diamond_carat?: number | null;
  gemstone?: string | null;
  ring_size?: string | null;
  size?: string | null;
  is_featured?: boolean;
  is_active?: boolean;
  primary_image?: string | null;
  image_url?: string | null;
  images?: string[] | ProductImage[];
  gallery?: string[] | ProductImage[];
  rating_avg?: number;
  reviews_count?: number;
  created_at?: string;
}

export interface CartItem {
  id: number;
  product_id: number;
  product_name?: string;
  product_sku?: string;
  product?: Product;
  product_data?: {
    image?: string | null;
    slug?: string;
    description?: string;
    size?: string | null;
    metal_type?: string | null;
  };
  quantity: number;
  unit_price: number;
  total_price?: number;
  line_total?: number;
  ring_size?: string | null;
  size?: string | null;
  selected_size?: string | null;
  options?: {
    size?: string | null;
    [key: string]: any;
  } | null;
  metal_type?: string | null;
  selected?: boolean;
}

export interface WishlistItem {
  id: number;
  product_id: number;
  user_id?: number | null;
  session_id?: string | null;
  product: Product;
  created_at?: string;
}

export interface CartResponse {
  items: CartItem[];
  subtotal: number;
  count: number;
}

export interface OrderItem {
  id: number;
  order_id: number;
  product_id: number;
  product_name: string;
  product_sku?: string | null;
  quantity: number;
  price: number;
  unit_price?: number;
  subtotal: number;
  total_price?: number;
  product?: Product;
}

export interface ShippingMethod {
  id: number;
  name: string;
  description: string;
  type: 'flat_rate' | 'free_shipping' | 'weight_based';
  cost: number;
  estimated_days: string;
}

export interface ShippingAddress {
  first_name: string;
  last_name: string;
  email: string;
  phone: string;
  address_line_1: string;
  barangay: string;
  city: string;
  province?: string | null;
  region: string;
  zip_code: string;
  address_option: 'default' | 'custom';
  save_as_default?: boolean;
  shipping_method_id?: number;
  shipping_method_name?: string;
  shipping_cost?: number;
}

export interface SavedPaymentMethod {
  id: number;
  type: string;
  card_type?: string | null;
  card_last_four?: string | null;
  card_holder_name?: string | null;
  card_expiry_month?: string | null;
  card_expiry_year?: string | null;
  gcash_name?: string | null;
  gcash_number?: string | null;
  is_default: boolean;
}

export interface CheckoutInitResponse {
  user: {
    id: number;
    first_name: string;
    last_name: string;
    email: string;
    phone?: string | null;
    street?: string | null;
    barangay?: string | null;
    city?: string | null;
    province?: string | null;
    region?: string | null;
    zip_code?: string | null;
    is_default_address_complete: boolean;
  };
  cart_items: CartItem[];
  subtotal: number;
  tax_amount: number;
  default_shipping_cost: number;
  total: number;
  currency: string;
  cod_eligible: boolean;
  available_shipping_methods: ShippingMethod[];
  default_shipping_method?: ShippingMethod | null;
  payment_methods: SavedPaymentMethod[];
}

export interface PsgcItem {
  code: string;
  name: string;
  type?: string;
  region_name?: string;
}

export interface Order {
  id: number;
  order_number: string;
  status: 'pending' | 'processing' | 'shipped' | 'delivered' | 'cancelled' | 'refunded';
  payment_status: 'pending' | 'paid' | 'failed' | 'refunded';
  payment_method: string;
  tracking_number?: string | null;
  courier?: string | null;
  estimated_delivery?: string | null;
  shipping_amount?: number;
  shipping_cost?: number;
  discount_amount?: number;
  tax_amount?: number;
  subtotal: number;
  total_amount: number;
  currency?: string;
  shipping_address?: any;
  billing_address?: any;
  order_items?: OrderItem[];
  items?: OrderItem[];
  created_at: string;
  updated_at?: string;
}

export interface ReturnRepair {
  id: number;
  rma_number: string;
  order_id: number;
  user_id: number;
  type: string;
  status: 'requested' | 'approved' | 'received' | 'processing' | 'repaired' | 'refunded' | 'completed' | 'rejected';
  reason: string;
  description: string;
  products?: Array<{ product_id: number; quantity: number }>;
  customer_notes?: string | null;
  admin_notes?: string | null;
  photos?: string[] | null;
  created_at: string;
  updated_at?: string;
  order?: Order;
}

export interface TrackingTimelineStage {
  stage: 'placed' | 'crafting' | 'transit' | 'delivered';
  title: string;
  description: string;
  date?: string | null;
  completed: boolean;
  current: boolean;
}

export interface OrderTrackingResult {
  id: number;
  order_number: string;
  status: string;
  payment_status: string;
  payment_method: string;
  tracking_number?: string | null;
  courier?: string | null;
  estimated_delivery?: string | null;
  subtotal: number;
  tax_amount: number;
  shipping_amount: number;
  total_amount: number;
  currency: string;
  created_at: string;
  items: Array<{
    id: number;
    product_id: number;
    product_name: string;
    product_sku: string;
    quantity: number;
    unit_price: number;
    total_price: number;
    image_url?: string | null;
  }>;
  shipping_address?: any;
  timeline: TrackingTimelineStage[];
}

