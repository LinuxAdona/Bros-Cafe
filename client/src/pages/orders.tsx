import { useState } from "react";
import { OrderCard } from "@/components/order-card";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { OrderStatus } from "@/components/order-status-badge";

//todo: remove mock functionality
const mockOrders = [
  { id: 1, orderNumber: "1234", customerName: "Sarah Johnson", items: ["2x Latte", "1x Croissant"], total: 12.50, status: "pending" as OrderStatus, timestamp: "5 mins ago" },
  { id: 2, orderNumber: "1235", customerName: "Mike Chen", items: ["1x Espresso", "1x Muffin"], total: 7.50, status: "preparing" as OrderStatus, timestamp: "12 mins ago" },
  { id: 3, orderNumber: "1236", customerName: "Emily Davis", items: ["1x Cappuccino", "2x Cookies"], total: 9.00, status: "ready" as OrderStatus, timestamp: "15 mins ago" },
  { id: 4, orderNumber: "1237", customerName: "Tom Wilson", items: ["3x Americano"], total: 9.00, status: "out-for-delivery" as OrderStatus, timestamp: "20 mins ago" },
  { id: 5, orderNumber: "1238", customerName: "Lisa Brown", items: ["1x Iced Latte", "1x Sandwich"], total: 11.50, status: "completed" as OrderStatus, timestamp: "1 hour ago" },
];

export default function Orders() {
  const [orders, setOrders] = useState(mockOrders);

  const handleStatusChange = (id: number, newStatus: OrderStatus) => {
    setOrders(orders.map(order => 
      order.id === id ? { ...order, status: newStatus } : order
    ));
  };

  const filterByStatus = (status?: OrderStatus) => {
    if (!status) return orders;
    return orders.filter(order => order.status === status);
  };

  return (
    <div className="p-6 space-y-6">
      <div>
        <h1 className="text-3xl font-bold">Order Management</h1>
        <p className="text-muted-foreground">Track and manage all orders</p>
      </div>

      <Tabs defaultValue="all" className="space-y-4">
        <TabsList>
          <TabsTrigger value="all" data-testid="tab-all-orders">All Orders</TabsTrigger>
          <TabsTrigger value="pending" data-testid="tab-pending">Pending</TabsTrigger>
          <TabsTrigger value="preparing" data-testid="tab-preparing">Preparing</TabsTrigger>
          <TabsTrigger value="ready" data-testid="tab-ready">Ready</TabsTrigger>
          <TabsTrigger value="delivery" data-testid="tab-delivery">Delivery</TabsTrigger>
        </TabsList>

        <TabsContent value="all" className="space-y-4">
          <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            {orders.map((order) => (
              <OrderCard key={order.id} {...order} onStatusChange={handleStatusChange} />
            ))}
          </div>
        </TabsContent>

        <TabsContent value="pending" className="space-y-4">
          <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            {filterByStatus("pending").map((order) => (
              <OrderCard key={order.id} {...order} onStatusChange={handleStatusChange} />
            ))}
          </div>
        </TabsContent>

        <TabsContent value="preparing" className="space-y-4">
          <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            {filterByStatus("preparing").map((order) => (
              <OrderCard key={order.id} {...order} onStatusChange={handleStatusChange} />
            ))}
          </div>
        </TabsContent>

        <TabsContent value="ready" className="space-y-4">
          <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            {filterByStatus("ready").map((order) => (
              <OrderCard key={order.id} {...order} onStatusChange={handleStatusChange} />
            ))}
          </div>
        </TabsContent>

        <TabsContent value="delivery" className="space-y-4">
          <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            {filterByStatus("out-for-delivery").map((order) => (
              <OrderCard key={order.id} {...order} onStatusChange={handleStatusChange} />
            ))}
          </div>
        </TabsContent>
      </Tabs>
    </div>
  );
}
