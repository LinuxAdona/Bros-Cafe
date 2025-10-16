import { Card, CardContent, CardHeader } from "@/components/ui/card";
import { OrderStatusBadge, OrderStatus } from "./order-status-badge";
import { Button } from "@/components/ui/button";
import { Clock, User } from "lucide-react";

interface OrderCardProps {
  id: number;
  orderNumber: string;
  customerName: string;
  items: string[];
  total: number;
  status: OrderStatus;
  timestamp: string;
  onStatusChange?: (id: number, newStatus: OrderStatus) => void;
}

export function OrderCard({ 
  id, 
  orderNumber, 
  customerName, 
  items, 
  total, 
  status, 
  timestamp,
  onStatusChange 
}: OrderCardProps) {
  const getNextStatus = (currentStatus: OrderStatus): OrderStatus | null => {
    const statusFlow: OrderStatus[] = ["pending", "preparing", "ready", "out-for-delivery", "completed"];
    const currentIndex = statusFlow.indexOf(currentStatus);
    return currentIndex < statusFlow.length - 1 ? statusFlow[currentIndex + 1] : null;
  };

  const nextStatus = getNextStatus(status);

  return (
    <Card>
      <CardHeader className="flex flex-row items-center justify-between gap-2 space-y-0 pb-3">
        <div>
          <h3 className="font-semibold" data-testid={`order-number-${id}`}>#{orderNumber}</h3>
          <div className="flex items-center gap-1 text-sm text-muted-foreground mt-1">
            <User className="h-3 w-3" />
            <span data-testid={`customer-name-${id}`}>{customerName}</span>
          </div>
        </div>
        <OrderStatusBadge status={status} />
      </CardHeader>
      <CardContent className="space-y-3">
        <div className="text-sm">
          {items.map((item, idx) => (
            <div key={idx} className="text-muted-foreground">{item}</div>
          ))}
        </div>
        <div className="flex items-center justify-between pt-2 border-t">
          <div className="flex items-center gap-1 text-xs text-muted-foreground">
            <Clock className="h-3 w-3" />
            <span>{timestamp}</span>
          </div>
          <span className="font-bold" data-testid={`order-total-${id}`}>${total.toFixed(2)}</span>
        </div>
        {nextStatus && onStatusChange && (
          <Button 
            className="w-full" 
            size="sm"
            onClick={() => onStatusChange(id, nextStatus)}
            data-testid={`button-update-status-${id}`}
          >
            Mark as {nextStatus.split('-').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ')}
          </Button>
        )}
      </CardContent>
    </Card>
  );
}
