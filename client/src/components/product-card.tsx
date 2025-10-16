import { Card, CardContent } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Plus } from "lucide-react";

interface ProductCardProps {
  id: number;
  name: string;
  price: number;
  image: string;
  category: string;
  stock?: number;
  onAddToCart?: (id: number) => void;
}

export function ProductCard({ id, name, price, image, stock, onAddToCart }: ProductCardProps) {
  return (
    <Card className="overflow-hidden hover-elevate active-elevate-2">
      <div className="aspect-square relative overflow-hidden bg-muted">
        <img 
          src={image} 
          alt={name}
          className="object-cover w-full h-full"
        />
      </div>
      <CardContent className="p-4">
        <h3 className="font-semibold mb-1" data-testid={`product-name-${id}`}>{name}</h3>
        <div className="flex items-center justify-between">
          <span className="text-lg font-bold" data-testid={`product-price-${id}`}>${price.toFixed(2)}</span>
          <Button 
            size="icon" 
            onClick={() => onAddToCart?.(id)}
            data-testid={`button-add-to-cart-${id}`}
          >
            <Plus className="h-4 w-4" />
          </Button>
        </div>
        {stock !== undefined && (
          <p className="text-xs text-muted-foreground mt-1">
            Stock: {stock}
          </p>
        )}
      </CardContent>
    </Card>
  );
}
