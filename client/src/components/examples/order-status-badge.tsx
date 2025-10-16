import { OrderStatusBadge } from '../order-status-badge';

export default function OrderStatusBadgeExample() {
  return (
    <div className="p-6 flex gap-2 flex-wrap">
      <OrderStatusBadge status="pending" />
      <OrderStatusBadge status="preparing" />
      <OrderStatusBadge status="ready" />
      <OrderStatusBadge status="out-for-delivery" />
      <OrderStatusBadge status="completed" />
      <OrderStatusBadge status="cancelled" />
    </div>
  );
}
