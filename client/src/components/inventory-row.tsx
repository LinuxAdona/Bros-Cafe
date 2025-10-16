import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Badge } from "@/components/ui/badge";
import { Pencil, Save, X } from "lucide-react";
import { useState } from "react";

interface InventoryRowProps {
  id: number;
  name: string;
  category: string;
  stock: number;
  minStock: number;
  price: number;
  onUpdate?: (id: number, stock: number) => void;
}

export function InventoryRow({ id, name, category, stock, minStock, price, onUpdate }: InventoryRowProps) {
  const [isEditing, setIsEditing] = useState(false);
  const [editStock, setEditStock] = useState(stock);

  const isLowStock = stock <= minStock;

  const handleSave = () => {
    onUpdate?.(id, editStock);
    setIsEditing(false);
  };

  const handleCancel = () => {
    setEditStock(stock);
    setIsEditing(false);
  };

  return (
    <tr className="border-b hover-elevate">
      <td className="p-4">
        <span className="font-medium" data-testid={`inventory-name-${id}`}>{name}</span>
      </td>
      <td className="p-4">
        <span className="text-sm text-muted-foreground">{category}</span>
      </td>
      <td className="p-4">
        {isEditing ? (
          <Input
            type="number"
            value={editStock}
            onChange={(e) => setEditStock(Number(e.target.value))}
            className="w-24"
            data-testid={`input-stock-${id}`}
          />
        ) : (
          <div className="flex items-center gap-2">
            <span data-testid={`inventory-stock-${id}`}>{stock}</span>
            {isLowStock && (
              <Badge variant="destructive" className="text-xs">
                Low
              </Badge>
            )}
          </div>
        )}
      </td>
      <td className="p-4">
        <span className="text-sm">${price.toFixed(2)}</span>
      </td>
      <td className="p-4">
        <div className="flex gap-2">
          {isEditing ? (
            <>
              <Button size="icon" variant="ghost" onClick={handleSave} data-testid={`button-save-${id}`}>
                <Save className="h-4 w-4" />
              </Button>
              <Button size="icon" variant="ghost" onClick={handleCancel} data-testid={`button-cancel-${id}`}>
                <X className="h-4 w-4" />
              </Button>
            </>
          ) : (
            <Button size="icon" variant="ghost" onClick={() => setIsEditing(true)} data-testid={`button-edit-${id}`}>
              <Pencil className="h-4 w-4" />
            </Button>
          )}
        </div>
      </td>
    </tr>
  );
}
