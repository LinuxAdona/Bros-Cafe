import { StatCard } from "@/components/stat-card";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { DollarSign, ShoppingBag, Package, TrendingUp, AlertCircle } from "lucide-react";
import { OrderCard } from "@/components/order-card";

//todo: remove mock functionality
const mockOrders = [
  { id: 1, orderNumber: "1234", customerName: "Sarah Johnson", items: ["2x Latte", "1x Croissant"], total: 12.50, status: "preparing" as const, timestamp: "5 mins ago" },
  { id: 2, orderNumber: "1235", customerName: "Mike Chen", items: ["1x Espresso", "1x Muffin"], total: 7.50, status: "ready" as const, timestamp: "12 mins ago" },
  { id: 3, orderNumber: "1236", customerName: "Emily Davis", items: ["1x Cappuccino", "2x Cookies"], total: 9.00, status: "pending" as const, timestamp: "15 mins ago" },
];

//todo: remove mock functionality
const lowStockItems = [
  { name: "Espresso Beans", stock: 5, unit: "kg" },
  { name: "Milk", stock: 8, unit: "L" },
  { name: "Sugar", stock: 3, unit: "kg" },
];

export default function Dashboard() {
  return (
    <div className="p-6 space-y-6">
      <div>
        <h1 className="text-3xl font-bold">Dashboard</h1>
        <p className="text-muted-foreground">Welcome back to Bro's Cafe</p>
      </div>

      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <StatCard
          title="Today's Revenue"
          value="$1,247"
          icon={DollarSign}
          trend={{ value: 12.5, isPositive: true }}
          subtitle="from yesterday"
        />
        <StatCard
          title="Orders Today"
          value="48"
          icon={ShoppingBag}
          trend={{ value: 8.2, isPositive: true }}
          subtitle="from yesterday"
        />
        <StatCard
          title="Items Sold"
          value="156"
          icon={TrendingUp}
          trend={{ value: 5.1, isPositive: true }}
          subtitle="from yesterday"
        />
        <StatCard
          title="Low Stock Items"
          value={lowStockItems.length}
          icon={Package}
          subtitle="need attention"
        />
      </div>

      <div className="grid gap-6 md:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle>Recent Orders</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            {mockOrders.map((order) => (
              <OrderCard
                key={order.id}
                {...order}
                onStatusChange={(id, status) => console.log('Status changed:', id, status)}
              />
            ))}
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between gap-2 space-y-0">
            <CardTitle>Low Stock Alerts</CardTitle>
            <AlertCircle className="h-5 w-5 text-destructive" />
          </CardHeader>
          <CardContent className="space-y-3">
            {lowStockItems.map((item, idx) => (
              <div key={idx} className="flex items-center justify-between p-3 rounded-md bg-muted">
                <div>
                  <p className="font-medium" data-testid={`low-stock-item-${idx}`}>{item.name}</p>
                  <p className="text-sm text-muted-foreground">Only {item.stock} {item.unit} left</p>
                </div>
                <Package className="h-5 w-5 text-destructive" />
              </div>
            ))}
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
