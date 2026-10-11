import { apiClient } from '@/api/client';

export interface ReviewAuthor {
  id?: number;
  name?: string;
}

export interface ReviewItem {
  id: number;
  product_id: number;
  user_id: number;
  order_id: number;
  rating: number;
  title?: string | null;
  review: string;
  is_verified_purchase: boolean;
  is_approved: boolean;
  created_at: string;
  user?: ReviewAuthor | null;
  author_name?: string;
  item_variant?: string;
}

export interface ReviewsResponse {
  success: boolean;
  reviews: {
    data: ReviewItem[];
    current_page: number;
    last_page: number;
    total: number;
  };
  average_rating: number;
  total_reviews: number;
}

export interface SubmitReviewPayload {
  product_id: number;
  rating: number;
  title?: string;
  review: string;
  order_id?: number;
}

export async function fetchReviews(productId: number | string, page = 1): Promise<ReviewsResponse> {
  const response = await apiClient.get<ReviewsResponse>(`/v1/reviews/${productId}`, {
    params: { page },
  });
  return response.data;
}

export async function submitReview(payload: SubmitReviewPayload): Promise<{ success: boolean; message: string; review?: ReviewItem }> {
  const response = await apiClient.post<{ success: boolean; message: string; review?: ReviewItem }>(
    '/v1/reviews/submit',
    payload
  );
  return response.data;
}
