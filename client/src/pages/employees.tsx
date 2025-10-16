import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Avatar, AvatarFallback } from "@/components/ui/avatar";
import { Badge } from "@/components/ui/badge";

//todo: remove mock functionality
const mockEmployees = [
  { id: 1, name: "John Smith", role: "Barista", shift: "Morning", sales: 1247, status: "active" },
  { id: 2, name: "Emma Wilson", role: "Manager", shift: "Full Day", sales: 2145, status: "active" },
  { id: 3, name: "Mike Johnson", role: "Barista", shift: "Evening", sales: 987, status: "active" },
  { id: 4, name: "Sarah Davis", role: "Cashier", shift: "Morning", sales: 1534, status: "off" },
];

export default function Employees() {
  return (
    <div className="p-6 space-y-6">
      <div>
        <h1 className="text-3xl font-bold">Employee Management</h1>
        <p className="text-muted-foreground">Track performance and manage shifts</p>
      </div>

      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        {mockEmployees.map((employee) => (
          <Card key={employee.id}>
            <CardHeader className="flex flex-row items-center justify-between gap-2 space-y-0">
              <div className="flex items-center gap-3">
                <Avatar>
                  <AvatarFallback>
                    {employee.name.split(' ').map(n => n[0]).join('')}
                  </AvatarFallback>
                </Avatar>
                <div>
                  <h3 className="font-semibold" data-testid={`employee-name-${employee.id}`}>{employee.name}</h3>
                  <p className="text-sm text-muted-foreground">{employee.role}</p>
                </div>
              </div>
              <Badge variant={employee.status === "active" ? "default" : "secondary"}>
                {employee.status}
              </Badge>
            </CardHeader>
            <CardContent className="space-y-3">
              <div className="flex justify-between text-sm">
                <span className="text-muted-foreground">Shift</span>
                <span className="font-medium">{employee.shift}</span>
              </div>
              <div className="flex justify-between text-sm">
                <span className="text-muted-foreground">Total Sales</span>
                <span className="font-bold" data-testid={`employee-sales-${employee.id}`}>${employee.sales}</span>
              </div>
            </CardContent>
          </Card>
        ))}
      </div>
    </div>
  );
}
