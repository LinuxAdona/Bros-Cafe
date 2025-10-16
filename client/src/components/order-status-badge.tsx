import { Badge } from "@/components/ui/badge";

export type OrderStatus = "pending" | "preparing" | "ready" | "out-for-delivery" | "completed" | "cancelled";

interface OrderStatusBadgeProps {
  status: OrderStatus;
}

const statusConfig: Record<OrderStatus, { label: string; className: string }> = {
  pending: { label: "Pending", className: "bg-blue-500 hover:bg-blue-500" },
  preparing: { label: "Preparing", className: "bg-yellow-500 hover:bg-yellow-500" },
  ready: { label: "Ready", className: "bg-chart-2 hover:bg-chart-2" },
  "out-for-delivery": { label: "Out for Delivery", className: "bg-purple-500 hover:bg-purple-500" },
  completed: { label: "Completed", className: "bg-muted hover:bg-muted text-muted-foreground" },
  cancelled: { label: "Cancelled", className: "bg-destructive hover:bg-destructive" },
};

export function OrderStatusBadge({ status }: OrderStatusBadgeProps) {
  const config = statusConfig[status];
  
  return (
    <Badge className={config.className} data-testid={`badge-order-status-${status}`}>
      {config.label}
    </Badge>
  );
}
