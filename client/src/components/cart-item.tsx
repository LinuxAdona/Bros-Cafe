import { Button } from "@/components/ui/button";
import { Minus, Plus, Trash2 } from "lucide-react";

interface CartItemProps {
  id: number;
  name: string;
  price: number;
  quantity: number;
  onUpdateQuantity?: (id: number, quantity: number) => void;
  onRemove?: (id: number) => void;
}

export function CartItem({ id, name, price, quantity, onUpdateQuantity, onRemove }: CartItemProps) {
  return (
    <div className="flex items-center justify-between py-3 border-b">
      <div className="flex-1">
        <h4 className="font-medium" data-testid={`cart-item-name-${id}`}>{name}</h4>
        <p className="text-sm text-muted-foreground">${price.toFixed(2)}</p>
      </div>
      <div className="flex items-center gap-3">
        <div className="flex items-center gap-2">
          <Button 
            size="icon" 
            variant="outline" 
            className="h-7 w-7"
            onClick={() => onUpdateQuantity?.(id, quantity - 1)}
            disabled={quantity <= 1}
            data-testid={`button-decrease-${id}`}
          >
            <Minus className="h-3 w-3" />
          </Button>
          <span className="w-8 text-center font-medium" data-testid={`cart-quantity-${id}`}>{quantity}</span>
          <Button 
            size="icon" 
            variant="outline" 
            className="h-7 w-7"
            onClick={() => onUpdateQuantity?.(id, quantity + 1)}
            data-testid={`button-increase-${id}`}
          >
            <Plus className="h-3 w-3" />
          </Button>
        </div>
        <Button 
          size="icon" 
          variant="ghost" 
          className="h-7 w-7"
          onClick={() => onRemove?.(id)}
          data-testid={`button-remove-${id}`}
        >
          <Trash2 className="h-3 w-3" />
        </Button>
      </div>
    </div>
  );
}
